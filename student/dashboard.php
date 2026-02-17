<?php
session_start();
include('config.php');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require './PHPMailer/src/Exception.php';
require './PHPMailer/src/PHPMailer.php';
require './PHPMailer/src/SMTP.php';

// ==========================
// Authentication Check
// ==========================
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Student') {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// ==========================
// Handle One-Click Registration
// ==========================
if (isset($_GET['register_event_id'])) {
    $event_id = intval($_GET['register_event_id']);

    $check = $conn->prepare("SELECT id FROM event_registrations WHERE user_id=? AND event_id=?");
    $check->bind_param("ii", $user_id, $event_id);
    $check->execute();
    $check->store_result();

    if ($check->num_rows === 0) {
        $insert = $conn->prepare("INSERT INTO event_registrations (user_id, event_id, status) VALUES (?, ?, 'pending')");
        $insert->bind_param("ii", $user_id, $event_id);
        $insert->execute();
        $insert->close();

        $_SESSION['alert_message'] = "Successfully registered for the event!";

        // Fetch event & user details for email
        $event_stmt = $conn->prepare("SELECT title, start_datetime FROM events WHERE event_id = ?");
        $event_stmt->bind_param("i", $event_id);
        $event_stmt->execute();
        $event_data = $event_stmt->get_result()->fetch_assoc();
        $event_stmt->close();

        $stmt_user = $conn->prepare("SELECT email, first_name, last_name FROM users WHERE id=?");
        $stmt_user->bind_param("i", $user_id);
        $stmt_user->execute();
        $user_data = $stmt_user->get_result()->fetch_assoc();
        $stmt_user->close();

        $name = trim($user_data['first_name'] . ' ' . $user_data['last_name']);
        $email = $user_data['email'];

        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'nasiryt.827@gmail.com';
            $mail->Password = 'ddgc zhft gohg bjmz';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            $mail->setFrom('nasiryt.827@gmail.com', 'VUCEMS');
            $mail->addAddress($email, $name);

            $mail->isHTML(true);
            $mail->Subject = 'Event Registration Confirmation';
            $mail->Body    = "
                <p>Hi {$name},</p>
                <p>You have successfully registered for <strong>{$event_data['title']}</strong>.</p>
                <p>Event starts on: ".date('d M Y • h:i A', strtotime($event_data['start_datetime']))."</p>
                <p>Thank you for joining!</p>
            ";
            $mail->send();
        } catch (Exception $e) {
            error_log("Mail Error: {$mail->ErrorInfo}");
        }

    } else {
        $_SESSION['alert_message'] = "You are already registered for this event.";
    }
    $check->close();
    header("Location: dashboard.php");
    exit;
}

// ==========================
// Fetch Student Name
// ==========================
$stmt = $conn->prepare("SELECT first_name, last_name FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$name = ($row = $result->fetch_assoc()) ? trim($row['first_name'] . ' ' . $row['last_name']) : "Student";
$stmt->close();

// ==========================
// Quick Stats
// ==========================
$upcoming_registered = $conn->prepare("
    SELECT COUNT(*) AS count
    FROM event_registrations er
    JOIN events e ON er.event_id = e.event_id
    WHERE er.user_id = ? AND e.start_datetime > NOW() AND er.status IN ('pending', 'confirmed')
");
$upcoming_registered->bind_param("i", $user_id);
$upcoming_registered->execute();
$upcoming_registered_count = $upcoming_registered->get_result()->fetch_assoc()['count'] ?? 0;
$upcoming_registered->close();

$attended = $conn->prepare("
    SELECT COUNT(DISTINCT er.event_id) AS count
    FROM event_registrations er
    WHERE er.user_id = ?
");
$attended->bind_param("i", $user_id);
$attended->execute();
$attended_count = $attended->get_result()->fetch_assoc()['count'] ?? 0;
$attended->close();

$pending_feedback = $conn->prepare("
    SELECT COUNT(DISTINCT er.event_id) AS count
    FROM event_registrations er
    LEFT JOIN event_feedback ef ON ef.event_id = er.event_id AND ef.user_id = er.user_id
    WHERE er.user_id = ? AND ef.id IS NULL
");
$pending_feedback->bind_param("i", $user_id);
$pending_feedback->execute();
$pending_feedback_count = $pending_feedback->get_result()->fetch_assoc()['count'] ?? 0;
$pending_feedback->close();

// ==========================
// Fetch Categories
// ==========================
$categories = [];
$catResult = $conn->query("SELECT id, name FROM event_categories ORDER BY name ASC");
while ($row = $catResult->fetch_assoc()) {
    $categories[] = $row;
}

// ==========================
// Filters
// ==========================
$search     = $_GET['search'] ?? '';
$category   = $_GET['category'] ?? '';
$from_date  = $_GET['from_date'] ?? date('Y-m-d');
$to_date    = $_GET['to_date'] ?? '';

// ==========================
// Fetch Upcoming Events
// ==========================
$sql = "
    SELECT 
        e.event_id, e.title, e.description, e.start_datetime, e.end_datetime,
        e.registration_deadline, e.banner_image, e.category_id, c.name AS category,
        er.status AS reg_status
    FROM events e
    LEFT JOIN event_categories c ON e.category_id = c.id
    LEFT JOIN event_registrations er ON er.event_id = e.event_id AND er.user_id = ?
    WHERE e.status = 'approved' AND e.start_datetime > NOW()
";

$params = [$user_id];
$types  = "i";

if ($search !== '') {
    $sql .= " AND e.title LIKE ?";
    $params[] = "%$search%";
    $types .= "s";
}
if ($category !== '') {
    $sql .= " AND e.category_id = ?";
    $params[] = $category;
    $types .= "i";
}
if ($from_date !== '') {
    $sql .= " AND e.start_datetime >= ?";
    $params[] = "$from_date 00:00:00";
    $types .= "s";
}
if ($to_date !== '') {
    $sql .= " AND e.start_datetime <= ?";
    $params[] = "$to_date 23:59:59";
    $types .= "s";
}

$sql .= " ORDER BY e.start_datetime ASC LIMIT 12";

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$events_result = $stmt->get_result();
$events = $events_result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// ==========================
// My Registered Events
// ==========================
$stmt = $conn->prepare("
    SELECT e.event_id, e.title, e.start_datetime, e.end_datetime, e.category_id, er.status AS reg_status
    FROM event_registrations er
    JOIN events e ON er.event_id = e.event_id
    WHERE er.user_id = ? ORDER BY e.start_datetime ASC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$my_registered = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// ==========================
// Recommended Events
// ==========================
$recommended = [];
$category_ids = array_unique(array_filter(array_column($my_registered, 'category_id')));
if (count($category_ids) > 0) {
    $placeholders = implode(',', array_fill(0, count($category_ids), '?'));
    $types = str_repeat('i', count($category_ids)) . 'i';
    $params = array_merge($category_ids, [$user_id]);

    $sql = "
        SELECT e.event_id, e.title, e.start_datetime, e.end_datetime, e.banner_image
        FROM events e
        WHERE e.status='approved' AND e.start_datetime > NOW() 
        AND e.category_id IN ($placeholders)
        AND e.event_id NOT IN (SELECT event_id FROM event_registrations WHERE user_id=?)
        ORDER BY e.start_datetime ASC LIMIT 6
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $recommended = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Student Dashboard | VUCEMS</title>
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
<link rel="stylesheet" href="../css/style.css">
<style>
.stat-card { transition: transform 0.2s; cursor: default; }
.stat-card:hover { transform: translateY(-5px); }
.event-card { min-height: 380px; transition: transform 0.2s; }
.event-card:hover { transform: translateY(-5px); box-shadow: 0 5px 15px rgba(0,0,0,0.2); }
.event-img { height: 160px; object-fit: cover; }
</style>
</head>
<body class="bg-light">
<?php include('navbar.php'); ?>

<div class="container-fluid mt-4 pb-5">

    <?php if (!empty($_SESSION['alert_message'])): ?>
        <div class="alert alert-info alert-dismissible fade show">
            <?= htmlspecialchars($_SESSION['alert_message']); unset($_SESSION['alert_message']); ?>
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    <?php endif; ?>

    <div class="card mb-4 shadow-lg text-white text-center py-4" style="background: linear-gradient(135deg, #28a745, #1e7e34);">
        <div class="card-body">
            <h2 class="mb-2 text-white">Student Dashboard</h2>
            <p class="lead mb-0">Welcome back, <strong><?= htmlspecialchars($name) ?></strong>!</p>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="row mb-5">
        <div class="col-md-4 mb-4">
            <div class="card stat-card shadow text-white" style="background: linear-gradient(135deg, #28a745, #1e7e34);">
                <div class="card-body text-center">
                    <i class="fas fa-calendar-check fa-3x mb-3"></i>
                    <h5>Upcoming Registered</h5>
                    <h3 class="font-weight-bold"><?= $upcoming_registered_count ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="card stat-card shadow text-white" style="background: linear-gradient(135deg, #17a2b8, #117a8b);">
                <div class="card-body text-center">
                    <i class="fas fa-check-circle fa-3x mb-3"></i>
                    <h5>Events Attended</h5>
                    <h3 class="font-weight-bold"><?= $attended_count ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="card stat-card shadow text-white" style="background: linear-gradient(135deg, #ffc107, #e0a800);">
                <div class="card-body text-center">
                    <i class="fas fa-comment-dots fa-3x mb-3"></i>
                    <h5>Pending Feedback</h5>
                    <h3 class="font-weight-bold"><?= $pending_feedback_count ?></h3>
                </div>
            </div>
        </div>
    </div>

        <!-- Recommended Events -->
    <h4 class="mb-3"><i class="fas fa-star mr-2"></i>Recommended Events</h4>
    <?php if (count($recommended) > 0): ?>
        <div class="row">
            <?php foreach ($recommended as $event): ?>
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card shadow-sm h-100 border-0 p-2">
                        <?php if (!empty($event['banner_image'])): ?>
                            <img src="../images/<?= htmlspecialchars($event['banner_image']) ?>" class="card-img-top event-img" alt="Event banner">
                        <?php else: ?>
                            <div class="bg-light text-center py-5 event-img"><i class="fas fa-image fa-3x text-muted"></i></div>
                        <?php endif; ?>
                        <h5 class="card-title mt-2"><?= htmlspecialchars($event['title']) ?></h5>
                        <p class="small mb-1"><strong>Start:</strong> <?= date('d M Y • h:i A', strtotime($event['start_datetime'])) ?></p>
                        <a href="dashboard.php?register_event_id=<?= $event['event_id'] ?>" class="btn btn-success btn-sm btn-block mt-2">Register</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="alert alert-info">No recommended events at the moment.</div>
    <?php endif; ?>

    <!-- Filters -->
    <div class="card mb-4 shadow-sm">
        <div class="card-body">
            <form method="GET" class="form-row align-items-end">
                <div class="col-md-3 mb-2">
                    <input type="text" name="search" class="form-control" placeholder="Search event title..." value="<?= htmlspecialchars($search) ?>">
                </div>
                <div class="col-md-2 mb-2">
                    <select name="category" class="form-control">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>" <?= ($category == $cat['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cat['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2 mb-2">
                    <input type="date" name="from_date" class="form-control" value="<?= htmlspecialchars($from_date) ?>">
                </div>
                <div class="col-md-2 mb-2">
                    <input type="date" name="to_date" class="form-control" value="<?= htmlspecialchars($to_date) ?>">
                </div>
                <div class="col-md-3 mb-2">
                    <button type="submit" class="btn btn-primary mr-2"><i class="fas fa-filter"></i> Filter</button>
                    <a href="dashboard.php" class="btn btn-outline-secondary"><i class="fas fa-redo"></i> Reset</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Upcoming Events -->
    <h4 class="mb-3"><i class="fas fa-calendar-alt mr-2"></i>Upcoming Events</h4>
    <?php if (count($events) > 0): ?>
        <div class="row">
            <?php foreach ($events as $event): ?>
                <?php
                $now = date('Y-m-d H:i:s');
                $deadline_passed = $event['registration_deadline'] && $event['registration_deadline'] < $now;
                $is_registered = !empty($event['reg_status']);
                ?>
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card event-card shadow-sm h-100 border-0">
                        <?php if (!empty($event['banner_image'])): ?>
                            <img src="../images/<?= htmlspecialchars($event['banner_image']) ?>" class="card-img-top event-img" alt="Event banner">
                        <?php else: ?>
                            <div class="bg-light text-center py-5 event-img"><i class="fas fa-image fa-3x text-muted"></i></div>
                        <?php endif; ?>
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title"><?= htmlspecialchars($event['title']) ?></h5>
                            <p class="text-muted small mb-2"><?= htmlspecialchars($event['category'] ?? 'General') ?></p>
                            <p class="small mb-2"><strong>Start:</strong> <?= date('d M Y • h:i A', strtotime($event['start_datetime'])) ?></p>
                            <?php if ($is_registered): ?>
                                <span class="badge badge-success mb-2"><?= ucfirst($event['reg_status']) ?></span>
                            <?php endif; ?>
                            <div class="mt-auto">
                                <?php if (!$is_registered && !$deadline_passed): ?>
                                    <a href="dashboard.php?register_event_id=<?= $event['event_id'] ?>" class="btn btn-success btn-sm btn-block mb-2">
                                        <i class="fas fa-sign-in-alt"></i> Register
                                    </a>
                                <?php elseif ($deadline_passed): ?>
                                    <button class="btn btn-secondary btn-sm btn-block mb-2" disabled>Registration Closed</button>
                                <?php endif; ?>
                                <div class="btn-group btn-group-sm w-100">
                                    <a href="event_details.php?event_id=<?= $event['event_id'] ?>" class="btn btn-outline-info">
                                        <i class="fas fa-info-circle"></i> Details
                                    </a>
                                    <a href="event_feedback.php?event_id=<?= $event['event_id'] ?>" class="btn btn-outline-warning">
                                        <i class="fas fa-comment-alt"></i> Feedback
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="alert alert-info text-center">No upcoming events match your filters.</div>
    <?php endif; ?>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>

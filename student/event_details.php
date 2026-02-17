<?php
session_start();
include('config.php');

// ==========================
// Auth check
// ==========================
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Student') {
    header("Location: ../login.php");
    exit;
}

$event_id = intval($_GET['event_id'] ?? 0);
if (!$event_id) {
    die("Invalid Event ID");
}

$user_id = $_SESSION['user_id'];

// ==========================
// Fetch Event Details
// ==========================
$sql = "
SELECT 
    e.*,
    c.name AS category_name,
    u.first_name AS organizer_first,
    u.last_name AS organizer_last
FROM events e
LEFT JOIN event_categories c ON e.category_id = c.id
LEFT JOIN users u ON e.organizer_id = u.id
WHERE e.event_id = ? AND e.status='approved'
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $event_id);
$stmt->execute();
$result = $stmt->get_result();
$event = $result->fetch_assoc();
$stmt->close();
if (!$event) die("Event not found or not approved");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Event Details | <?= htmlspecialchars($event['title']); ?></title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<?php include('navbar.php'); ?>

<div class="container mt-5">

    <!-- Event Details Card -->
    <div class="card shadow mb-4">
        <?php if (!empty($event['banner_image'])): ?>
            <img src="../images/<?= htmlspecialchars($event['banner_image']); ?>" class="card-img-top" style="height:300px; object-fit:cover;">
        <?php endif; ?>
        <div class="card-body">
            <h3 class="card-title"><?= htmlspecialchars($event['title']); ?></h3>
            <p class="text-muted mb-2">
                <strong>Category:</strong> <?= htmlspecialchars($event['category_name'] ?? 'N/A'); ?><br>
                <strong>Organizer:</strong> <?= htmlspecialchars($event['organizer_first'].' '.$event['organizer_last']); ?><br>
                <strong>Type:</strong> <?= ucfirst($event['event_type']); ?><br>
                <strong>Status:</strong> <?= ucfirst($event['status']); ?><br>
                <strong>Capacity:</strong> <?= intval($event['capacity']); ?>
            </p>
            <p>
                <strong>Start:</strong> <?= $event['start_datetime']; ?><br>
                <strong>End:</strong> <?= $event['end_datetime']; ?><br>
                <strong>Registration Deadline:</strong> <?= $event['registration_deadline'] ?? 'N/A'; ?>
            </p>
            <p><?= nl2br(htmlspecialchars($event['description'])); ?></p>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

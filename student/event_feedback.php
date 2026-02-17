<?php
session_start();
include('config.php');

// Auth check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Student') {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Get event_id
$event_id = isset($_GET['event_id']) ? intval($_GET['event_id']) : 0;

// Fetch event details
$stmt = $conn->prepare("SELECT title FROM events WHERE event_id=?");
$stmt->bind_param("i", $event_id);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $event_title = $row['title'];
} else {
    die("Event not found.");
}
$stmt->close();

// Handle feedback submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['rating'], $_POST['feedback_text'])) {
    $rating = intval($_POST['rating']);
    $feedback_text = trim($_POST['feedback_text']);

    // Check if feedback already exists for this user & event
    $check = $conn->prepare("SELECT id FROM event_feedback WHERE user_id=? AND event_id=?");
    $check->bind_param("ii", $user_id, $event_id);
    $check->execute();
    $check->store_result();

    if ($check->num_rows === 0) {
        $insert = $conn->prepare("INSERT INTO event_feedback (user_id, event_id, rating, feedback_text) VALUES (?, ?, ?, ?)");
        $insert->bind_param("iiis", $user_id, $event_id, $rating, $feedback_text);
        $insert->execute();
        $insert->close();
        $message = "Feedback submitted successfully!";
    } else {
        $message = "You have already submitted feedback for this event.";
    }
    $check->close();
}

// Fetch all feedbacks for this event
$feedbacks = [];
$stmt = $conn->prepare("
    SELECT ef.rating, ef.feedback_text, ef.submitted_at, u.first_name, u.last_name
    FROM event_feedback ef
    JOIN users u ON ef.user_id = u.id
    WHERE ef.event_id=?
    ORDER BY ef.submitted_at DESC
");
$stmt->bind_param("i", $event_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $feedbacks[] = $row;
}
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Event Feedback | <?= htmlspecialchars($event_title) ?></title>
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
<link rel="stylesheet" href="../css/style.css">
</head>
<body>

<?php include('navbar.php'); ?>

<div class="container mt-4">

    <h3 class="mb-4">Feedback for: <strong><?= htmlspecialchars($event_title) ?></strong></h3>

    <?php if (!empty($message)): ?>
        <div class="alert alert-info"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <!-- Submit Feedback -->
    <div class="card mb-4 shadow-sm">
        <div class="card-body">
            <h5 class="mb-3">Submit Your Feedback</h5>
            <form method="POST">
                <div class="form-group">
                    <label>Rating (1 to 5)</label>
                    <select name="rating" class="form-control" required>
                        <option value="">Select Rating</option>
                        <?php for($i=1; $i<=5; $i++): ?>
                            <option value="<?= $i ?>"><?= $i ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Feedback</label>
                    <textarea name="feedback_text" class="form-control" rows="4" required></textarea>
                </div>
                <button type="submit" class="btn btn-success">Submit Feedback</button>
            </form>
        </div>
    </div>

    <!-- List of Feedbacks -->
    <h5 class="mb-3">All Feedbacks</h5>
    <?php if (count($feedbacks) > 0): ?>
        <?php foreach ($feedbacks as $fb): ?>
            <div class="card mb-3 shadow-sm">
                <div class="card-body">
                    <h6><?= htmlspecialchars($fb['first_name'].' '.$fb['last_name']) ?> <small class="text-muted"><?= date('d M Y • h:i A', strtotime($fb['submitted_at'])) ?></small></h6>
                    <p>Rating: <?= $fb['rating'] ?>/5</p>
                    <p><?= nl2br(htmlspecialchars($fb['feedback_text'])) ?></p>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="alert alert-info">No feedbacks submitted yet for this event.</div>
    <?php endif; ?>


</div>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
session_start();
include('config.php');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Student') {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$alert_message = "";

/* ===============================
   DELETE REGISTRATION
================================ */
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);

    // Check if status is pending before deleting
    $check = $conn->prepare("SELECT status FROM event_registrations WHERE id=? AND user_id=?");
    $check->bind_param("ii", $delete_id, $user_id);
    $check->execute();
    $res = $check->get_result();
    if ($res->num_rows > 0) {
        $row = $res->fetch_assoc();
        if ($row['status'] === 'pending') {
            $del = $conn->prepare("DELETE FROM event_registrations WHERE id=? AND user_id=?");
            $del->bind_param("ii", $delete_id, $user_id);
            if ($del->execute()) {
                $alert_message = "Registration deleted successfully.";
            } else {
                $alert_message = "Failed to delete registration.";
            }
            $del->close();
        } else {
            $alert_message = "Only pending registrations can be deleted.";
        }
    }
    $check->close();
}

/* ===============================
   FETCH USER REGISTRATIONS
================================ */
$sql = "
SELECT 
    er.id AS registration_id,
    er.status,
    er.registered_at,
    er.cancellation_reason,
    e.title,
    e.start_datetime,
    e.end_datetime,
    e.status AS event_status
FROM event_registrations er
JOIN events e ON er.event_id = e.event_id
WHERE er.user_id = ?
ORDER BY er.registered_at DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Event Registrations</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<?php include('navbar.php'); ?>

<div class="container mt-5">
    <h3 class="mb-4">My Event Registrations</h3>

    <?php if($alert_message): ?>
        <div class="alert alert-info alert-dismissible fade show">
            <?= htmlspecialchars($alert_message); ?>
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    <?php endif; ?>

    <table class="table table-bordered table-hover">
        <thead class="thead-dark">
        <tr>
            <th>Event</th>
            <th>Event Date</th>
            <th>Status</th>
            <th>Registered On</th>
            <th>Reason</th>
        </tr>
        </thead>
        <tbody>
        <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['title']); ?></td>
                    <td><?= date("d M Y, h:i A", strtotime($row['start_datetime'])); ?></td>
                    <td>
                        <span class="badge badge-info"><?= ucfirst($row['status']); ?></span>
                    </td>
                    <td><?= date("d M Y", strtotime($row['registered_at'])); ?></td>
                    <td><?= htmlspecialchars($row['cancellation_reason'] ?? ''); ?></td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="5" class="text-center">No registrations found</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<script>
function confirmDelete(id) {
    if(confirm("Are you sure you want to delete this registration?")) {
        window.location.href = "my_registrations.php?delete_id=" + id;
    }
}
</script>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

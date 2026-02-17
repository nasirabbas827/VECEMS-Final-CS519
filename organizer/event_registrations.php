<?php
session_start();
include('config.php');

// Auth check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Event Organizer') {
    header("Location: ../login.php");
    exit;
}

$organizer_id = $_SESSION['user_id'];

// Handle event filter
$selected_event = isset($_GET['event_id']) ? intval($_GET['event_id']) : 0;

/* ===============================
   FETCH EVENTS FOR FILTER
================================ */
$events_sql = "SELECT event_id, title FROM events WHERE organizer_id = ?";
$events_stmt = $conn->prepare($events_sql);
$events_stmt->bind_param("i", $organizer_id);
$events_stmt->execute();
$events_result = $events_stmt->get_result();

/* ===============================
   FETCH REGISTRATIONS
================================ */
$reg_sql = "
SELECT 
    er.id AS registration_id,
    er.status AS registration_status,
    er.cancellation_reason,
    er.registered_at,
    u.first_name,
    u.last_name,
    u.email,
    e.title AS event_title,
    e.event_id
FROM event_registrations er
INNER JOIN events e ON er.event_id = e.event_id
INNER JOIN users u  ON er.user_id = u.id
WHERE e.organizer_id = ? ";

// Apply event filter if selected
if ($selected_event > 0) {
    $reg_sql .= " AND e.event_id = $selected_event ";
}

$reg_sql .= " ORDER BY er.registered_at DESC ";

$stmt = $conn->prepare($reg_sql);
$stmt->bind_param("i", $organizer_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Event Registrations | Organizer</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<?php include('navbar.php'); ?>

<div id="content">
<div class="container mt-5">
    <h3 class="mb-4">Registrations for My Events</h3>

    <!-- FILTER AND EXPORT -->
    <form method="GET" class="form-inline mb-3">
        <label class="mr-2">Select Event:</label>
        <select name="event_id" class="form-control mr-2">
            <option value="0">All Events</option>
            <?php while($event = $events_result->fetch_assoc()): ?>
                <option value="<?= $event['event_id']; ?>" <?= $selected_event == $event['event_id'] ? 'selected' : ''; ?>>
                    <?= htmlspecialchars($event['title']); ?>
                </option>
            <?php endwhile; ?>
        </select>
        <button type="submit" class="btn btn-primary mr-2">Filter</button>
        <a href="export_registrations.php?event_id=<?= $selected_event; ?>" class="btn btn-success">Export CSV</a>
    </form>

    <table class="table table-bordered table-hover">
        <thead class="thead-dark">
        <tr>
            <th>Event</th>
            <th>Student</th>
            <th>Email</th>
            <th>Status</th>
            <th>Registered On</th>
            <th>Action</th>
        </tr>
        </thead>
        <tbody>
        <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['event_title']); ?></td>
                    <td><?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></td>
                    <td><?= htmlspecialchars($row['email']); ?></td>
                    <td><span class="badge badge-info"><?= ucfirst($row['registration_status']); ?></span></td>
                    <td><?= date("d M Y", strtotime($row['registered_at'])); ?></td>
                    <td>
                        <button class="btn btn-sm btn-primary" data-toggle="modal" data-target="#regModal<?= $row['registration_id']; ?>">
                            View / Update
                        </button>
                    </td>
                </tr>

                <!-- MODAL -->
                <div class="modal fade" id="regModal<?= $row['registration_id']; ?>">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <form method="POST" action="update_registration.php">
                                <div class="modal-header">
                                    <h5 class="modal-title">
                                        Registration Details – <?= htmlspecialchars($row['event_title']); ?>
                                    </h5>
                                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                                </div>
                                <div class="modal-body">
                                    <input type="hidden" name="registration_id" value="<?= $row['registration_id']; ?>">

                                    <div class="form-group">
                                        <label>Registration Status</label>
                                        <select name="status" class="form-control">
                                            <?php
                                            $statuses = ['pending','confirmed','waitlisted','cancelled','attended','no_show'];
                                            foreach ($statuses as $status):
                                            ?>
                                                <option value="<?= $status; ?>" <?= $row['registration_status'] === $status ? 'selected' : ''; ?>>
                                                    <?= ucfirst($status); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <div class="form-group">
                                        <label>Cancellation Reason (Organizer Only)</label>
                                        <textarea name="cancellation_reason" class="form-control" rows="3"><?= htmlspecialchars($row['cancellation_reason']); ?></textarea>
                                    </div>
                                </div>

                                <div class="modal-footer">
                                    <button type="submit" class="btn btn-success">Update Registration</button>
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="6" class="text-center">No registrations found.</td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

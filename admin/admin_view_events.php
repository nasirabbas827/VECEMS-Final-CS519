<?php
session_start();
include('config.php');

// ==========================
// Authentication Check
// ==========================
if (!isset($_SESSION["usertype"]) || $_SESSION["usertype"] !== "admin") {
    header("Location: admin_login.php");
    exit;
}

// ==========================
// Handle Status Update
// ==========================
if (isset($_POST['update_status'])) {
    $event_id = intval($_POST['event_id']);
    $new_status = $_POST['status'];

    $stmt = $conn->prepare("UPDATE events SET status=?, updated_at=NOW() WHERE event_id=?");
    $stmt->bind_param("si", $new_status, $event_id);
    
    if ($stmt->execute()) {
        $_SESSION['alert_message'] = "Event status updated to " . ucfirst($new_status) . " successfully.";
    }
    $stmt->close();

    header("Location: admin_view_events.php");
    exit;
}

// ==========================
// Fetch Events
// ==========================
$sql = "SELECT e.*, u.first_name, u.last_name, u.email as organizer_email, c.name AS category_name 
        FROM events e
        JOIN users u ON e.organizer_id = u.id
        LEFT JOIN event_categories c ON e.category_id = c.id
        ORDER BY e.created_at DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Events | Admin</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .badge-pending { background-color: #ffc107; color: #212529; }
        .badge-approved { background-color: #28a745; color: #fff; }
        .badge-rejected { background-color: #dc3545; color: #fff; }
        .badge-cancelled { background-color: #6c757d; color: #fff; }
        .badge-completed { background-color: #17a2b8; color: #fff; }
    </style>
</head>
<body class="bg-light">

<?php include('admin_navbar.php'); ?>

<div class="container-fluid mt-4 pb-5">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3><i class="fas fa-calendar-alt mr-2"></i>Event Management</h3>
    </div>

    <?php if (isset($_SESSION['alert_message'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?= $_SESSION['alert_message']; unset($_SESSION['alert_message']); ?>
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    <?php endif; ?>

            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="thead-dark">
                        <tr>
                            <th>ID</th>
                            <th>Event Details</th>
                            <th>Organizer</th>
                            <th>Type/Venue</th>
                            <th>Schedule</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= $row['event_id'] ?></td>
                            <td>
                                <strong><?= htmlspecialchars($row['title']) ?></strong><br>
                                <small class="text-muted"><?= htmlspecialchars($row['category_name'] ?? 'Uncategorized') ?></small>
                            </td>
                            <td>
                                <?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) ?><br>
                                <small class="text-muted"><?= htmlspecialchars($row['organizer_email']) ?></small>
                            </td>
                            <td>
                                <span class="badge badge-secondary"><?= ucfirst($row['event_type']) ?></span><br>
                                <small class="text-truncate" style="max-width: 150px; display: inline-block;">
                                    <?= $row['event_type'] == 'physical' ? htmlspecialchars($row['venue']) : htmlspecialchars($row['meeting_link']) ?>
                                </small>
                            </td>
                            <td>
                                <small><b>Start:</b> <?= date('d M Y, h:i A', strtotime($row['start_datetime'])) ?></small><br>
                                <small><b>End:</b> <?= date('d M Y, h:i A', strtotime($row['end_datetime'])) ?></small>
                            </td>
                            <td>
                                <span class="badge badge-<?= $row['status'] ?>">
                                    <?= ucfirst($row['status']) ?>
                                </span>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-info" data-toggle="modal" data-target="#viewModal<?= $row['event_id'] ?>" title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-outline-danger" data-toggle="modal" data-target="#statusModal<?= $row['event_id'] ?>" title="Update Status">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>

                        <div class="modal fade" id="viewModal<?= $row['event_id'] ?>" tabindex="-1">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title"><?= htmlspecialchars($row['title']) ?></h5>
                                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="row">
                                            <div class="col-md-5">
                                                <?php if(!empty($row['banner_image'])): ?>
                                                    <img src="../images/<?= htmlspecialchars($row['banner_image']) ?>" class="img-fluid rounded shadow-sm mb-3">
                                                <?php else: ?>
                                                    <div class="bg-light text-center py-5 rounded mb-3"><i class="fas fa-image fa-3x text-muted"></i></div>
                                                <?php endif; ?>
                                            </div>
                                            <div class="col-md-7">
                                                <p><strong><i class="fas fa-align-left mr-2"></i>Description:</strong><br><?= nl2br(htmlspecialchars($row['description'])) ?></p>
                                                <hr>
                                                <div class="row">
                                                    <div class="col-6">
                                                        <p><strong>Type:</strong> <?= ucfirst($row['event_type']) ?></p>
                                                        <p><strong>Capacity:</strong> <?= $row['capacity'] > 0 ? $row['capacity'] : 'Unlimited' ?></p>
                                                    </div>
                                                    <div class="col-6">
                                                        <p><strong>Category:</strong> <?= htmlspecialchars($row['category_name'] ?? 'N/A') ?></p>
                                                        <p><strong>Deadline:</strong><br><small><?= $row['registration_deadline'] ? date('d M Y, H:i', strtotime($row['registration_deadline'])) : 'None' ?></small></p>
                                                    </div>
                                                </div>
                                                <p><strong>Location/Link:</strong><br>
                                                    <span class="text-primary"><?= $row['event_type'] == 'physical' ? htmlspecialchars($row['venue']) : '<a href="'.$row['meeting_link'].'" target="_blank">'.$row['meeting_link'].'</a>' ?></span>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="modal fade" id="statusModal<?= $row['event_id'] ?>" tabindex="-1">
                            <div class="modal-dialog modal-sm">
                                <div class="modal-content">
                                    <form method="POST">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Update Status</h5>
                                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                                        </div>
                                        <div class="modal-body">
                                            <input type="hidden" name="event_id" value="<?= $row['event_id'] ?>">
                                            <div class="form-group">
                                                <label class="font-weight-bold">Set Status For:</label>
                                                <p class="small text-muted"><?= htmlspecialchars($row['title']) ?></p>
                                                <select name="status" class="form-control shadow-sm" required>
                                                    <?php 
                                                    $statuses = ['pending', 'approved', 'rejected', 'cancelled', 'completed'];
                                                    foreach($statuses as $st): ?>
                                                        <option value="<?= $st ?>" <?= $row['status']==$st?'selected':'' ?>><?= ucfirst($st) ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="submit" name="update_status" class="btn btn-primary btn-block">Confirm Update</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
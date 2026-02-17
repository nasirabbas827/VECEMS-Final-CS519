<?php
session_start();
include('config.php');

// ────────────────────────────────────────────────
//  Authentication – only admin
// ────────────────────────────────────────────────
// Admin authentication
if (!isset($_SESSION["usertype"]) || $_SESSION["usertype"] !== "admin") {
    header("Location: admin_login.php");
    exit;
}


// ────────────────────────────────────────────────
//  Statistics – system-wide overview
// ────────────────────────────────────────────────

// 1. Total users by role (Matches your ENUM: Student, Faculty, Event Organizer)
$users_by_role = ['Student' => 0, 'Faculty' => 0, 'Event Organizer' => 0];
$result = $conn->query("SELECT role, COUNT(*) as cnt FROM users GROUP BY role");
while ($row = $result->fetch_assoc()) {
    $users_by_role[$row['role']] = $row['cnt'];
}
$total_users = array_sum($users_by_role);

// 2. Events – total + by status
$events_status = ['pending' => 0, 'approved' => 0, 'rejected' => 0, 'cancelled' => 0, 'completed' => 0];
$result = $conn->query("SELECT status, COUNT(*) as cnt FROM events GROUP BY status");
while ($row = $result->fetch_assoc()) {
    $events_status[$row['status']] = $row['cnt'];
}
$total_events = array_sum($events_status);

// 3. Registrations – total + attended (Using your registration status enum)
$total_registrations = $conn->query("SELECT COUNT(*) FROM event_registrations")->fetch_row()[0] ?? 0;
$total_attended      = $conn->query("SELECT COUNT(*) FROM event_registrations WHERE status = 'attended'")->fetch_row()[0] ?? 0;

// 4. Average event rating
$avg_rating_query = $conn->query("SELECT ROUND(AVG(rating), 1) as avg FROM event_feedback");
$avg_rating = $avg_rating_query->fetch_assoc()['avg'] ?? 0;

// 5. Pending approvals (Events + New User Accounts)
$pending_events = $events_status['pending'];
$pending_users  = $conn->query("SELECT COUNT(*) FROM users WHERE status = 'pending'")->fetch_row()[0] ?? 0;
$total_pending_approvals = $pending_events + $pending_users;

// 6. Recent activity – last 10 events
$recent_events = $conn->query("
    SELECT e.event_id, e.title, e.status, e.created_at, 
           u.first_name, u.last_name,
           c.name AS category
    FROM events e
    LEFT JOIN users u ON e.organizer_id = u.id
    LEFT JOIN event_categories c ON e.category_id = c.id
    ORDER BY e.created_at DESC LIMIT 10
");

// 7. Recent feedback
$recent_feedback = $conn->query("
    SELECT ef.rating, ef.feedback_text, ef.submitted_at,
           e.title AS event_title,
           CONCAT(u.first_name, ' ', u.last_name) AS student_name
    FROM event_feedback ef
    JOIN events e ON ef.event_id = e.event_id
    JOIN users u ON ef.user_id = u.id
    ORDER BY ef.submitted_at DESC LIMIT 8
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | VUCEMS</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="../css/style.css">

    <style>
        .welcome-card { background: linear-gradient(135deg, #4e73df 0%, #224abe 100%); color: white; border: none; border-radius: 15px; }
        .stat-card { border: none; border-radius: 12px; transition: transform 0.2s; box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.1); }
        .stat-card:hover { transform: translateY(-5px); }
        .icon-bg { position: absolute; top: 10px; right: 15px; font-size: 2.5rem; opacity: 0.2; }
        .chart-container { position: relative; height: 280px; }
        .list-group-item { border-left: 5px solid #e3e6f0; margin-bottom: 2px; }
        .border-left-pending { border-left-color: #f6c23e !important; }
        .border-left-approved { border-left-color: #1cc88a !important; }
        .border-left-rejected { border-left-color: #e74a3b !important; }
    </small>
    </style>
</head>
<body>

<?php include('admin_navbar.php'); ?>

<div class="container-fluid mt-4 pb-5">

    <div class="card welcome-card mb-4 shadow">
        <div class="card-body py-4 px-5">
            <h2 class="font-weight-bold text-white">System Administration</h2>
            <p class="lead mb-0">Overview of users, events, and pending system tasks.</p>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card bg-primary text-white h-100">
                <div class="card-body">
                    <i class="fas fa-users icon-bg"></i>
                    <div class="text-white-50 small font-weight-bold text-uppercase mb-1">Total Users</div>
                    <div class="h3 mb-0 font-weight-bold"><?= number_format($total_users) ?></div>
                    <div class="mt-2 small"><?= $users_by_role['Student'] ?> Students registered</div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card bg-success text-white h-100">
                <div class="card-body">
                    <i class="fas fa-calendar icon-bg"></i>
                    <div class="text-white-50 small font-weight-bold text-uppercase mb-1">Active Events</div>
                    <div class="h3 mb-0 font-weight-bold"><?= number_format($events_status['approved']) ?></div>
                    <div class="mt-2 small"><?= $total_events ?> total events in system</div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card bg-info text-white h-100">
                <div class="card-body">
                    <i class="fas fa-clipboard-list icon-bg"></i>
                    <div class="text-white-50 small font-weight-bold text-uppercase mb-1">Attendance</div>
                    <div class="h3 mb-0 font-weight-bold"><?= number_format($total_attended) ?></div>
                    <div class="mt-2 small">From <?= number_format($total_registrations) ?> total signups</div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card bg-warning text-white h-100">
                <div class="card-body">
                    <i class="fas fa-exclamation-triangle icon-bg"></i>
                    <div class="text-white-50 small font-weight-bold text-uppercase mb-1">Pending Review</div>
                    <div class="h3 mb-0 font-weight-bold"><?= $total_pending_approvals ?></div>
                    <div class="mt-2 small"><?= $pending_events ?> events • <?= $pending_users ?> new users</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-lg-4 mb-4">
            <div class="card shadow h-100">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Event Status Distribution</h6>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="eventsStatusChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4 mb-4">
            <div class="card shadow h-100 border-bottom-success">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-success">Public Sentiment</h6>
                </div>
                <div class="card-body text-center d-flex flex-column justify-content-center">
                    <div class="display-3 font-weight-bold text-success"><?= $avg_rating ?></div>
                    <div class="text-muted mb-4">Average System Rating</div>
                    <div class="h5"><?= $conn->query("SELECT COUNT(*) FROM event_feedback")->fetch_row()[0] ?? 0 ?></div>
                    <p class="small text-muted">Total Reviews Submitted</p>
                </div>
            </div>
        </div>

        <div class="col-lg-4 mb-4">
            <div class="card shadow h-100">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">User Roles</h6>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="usersRoleChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-7 mb-4">
            <div class="card shadow h-100">
                <div class="card-header py-3 d-flex justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Recent Event Submissions</h6>
                    <a href="admin_view_events.php" class="small font-weight-bold text-primary">View All</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th>Event</th>
                                    <th>Organizer</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($e = $recent_events->fetch_assoc()): ?>
                                <tr>
                                    <td><span class="font-weight-bold text-dark"><?= htmlspecialchars($e['title']) ?></span></td>
                                    <td><?= htmlspecialchars($e['first_name'] . ' ' . $e['last_name']) ?></td>
                                    <td>
                                        <span class="badge badge-pill badge-<?= 
                                            $e['status'] == 'approved' ? 'success' : ($e['status'] == 'pending' ? 'warning' : 'danger') 
                                        ?>"><?= ucfirst($e['status']) ?></span>
                                    </td>
                                    <td class="small text-muted"><?= date('M d, Y', strtotime($e['created_at'])) ?></td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-5 mb-4">
            <div class="card shadow h-100">
                <div class="card-header py-3 d-flex justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Student Feedback</h6>
                    <a href="manage_feedback.php" class="small font-weight-bold text-primary">Manage</a>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        <?php while($f = $recent_feedback->fetch_assoc()): ?>
                        <div class="list-group-item p-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="font-weight-bold small text-primary"><?= htmlspecialchars($f['event_title']) ?></span>
                                <span class="text-warning small"><?= str_repeat('★', $f['rating']) ?></span>
                            </div>
                            <p class="mb-1 small text-dark"><?= htmlspecialchars(substr($f['feedback_text'], 0, 80)) ?>...</p>
                            <small class="text-muted"><?= htmlspecialchars($f['student_name']) ?> • <?= date('M d, H:i', strtotime($f['submitted_at'])) ?></small>
                        </div>
                        <?php endwhile; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Chart Colors
const colors = {
    primary: '#4e73df',
    success: '#1cc88a',
    info: '#36b9cc',
    warning: '#f6c23e',
    danger: '#e74a3b',
    secondary: '#858796'
};

// Events Status Chart
new Chart(document.getElementById('eventsStatusChart'), {
    type: 'doughnut',
    data: {
        labels: ['Pending', 'Approved', 'Rejected', 'Cancelled', 'Completed'],
        datasets: [{
            data: [
                <?= $events_status['pending'] ?>, 
                <?= $events_status['approved'] ?>, 
                <?= $events_status['rejected'] ?>, 
                <?= $events_status['cancelled'] ?>, 
                <?= $events_status['completed'] ?>
            ],
            backgroundColor: [colors.warning, colors.success, colors.danger, colors.secondary, colors.info],
            hoverOffset: 4
        }]
    },
    options: { maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }
});

// Users Role Chart
new Chart(document.getElementById('usersRoleChart'), {
    type: 'pie',
    data: {
        labels: ['Students', 'Faculty', 'Organizers'],
        datasets: [{
            data: [
                <?= $users_by_role['Student'] ?>, 
                <?= $users_by_role['Faculty'] ?>, 
                <?= $users_by_role['Event Organizer'] ?>
            ],
            backgroundColor: [colors.primary, colors.info, colors.warning]
        }]
    },
    options: { maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }
});
</script>

</body>
</html>
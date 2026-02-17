<?php
session_start();
include('config.php');

// Authentication Check 
if (!isset($_SESSION['user_id']) || !isset($_SESSION["role"]) || $_SESSION["role"] !== "Event Organizer") {
    header("Location: ../login.php");
    exit;
}

$organizer_id = $_SESSION['user_id'];

// 1. Fetch user info
$stmt = $conn->prepare("SELECT first_name, last_name FROM users WHERE id = ? AND role = 'Event Organizer'");
$stmt->bind_param("i", $organizer_id);
$stmt->execute();
$user_row = $stmt->get_result()->fetch_assoc();
$name = $user_row ? trim($user_row['first_name'] . ' ' . $user_row['last_name']) : "Organizer";

// 2. Total events by status
$stmt = $conn->prepare("SELECT status, COUNT(*) as count FROM events WHERE organizer_id = ? GROUP BY status");
$stmt->bind_param("i", $organizer_id);
$stmt->execute();
$status_result = $stmt->get_result();
$event_status = ['pending' => 0, 'approved' => 0, 'rejected' => 0, 'cancelled' => 0, 'completed' => 0];
while ($row = $status_result->fetch_assoc()) {
    $event_status[$row['status']] = $row['count'];
}
$total_events = array_sum($event_status);

// 3. Physical vs Virtual Breakdown
$stmt = $conn->prepare("SELECT event_type, COUNT(*) as count FROM events WHERE organizer_id = ? GROUP BY event_type");
$stmt->bind_param("i", $organizer_id);
$stmt->execute();
$type_result = $stmt->get_result();
$event_types = ['physical' => 0, 'virtual' => 0];
while ($row = $type_result->fetch_assoc()) {
    $event_types[$row['event_type']] = $row['count'];
}

// 4. Registration & Attendance (Corrected to use event_registrations status)
$stmt = $conn->prepare("
    SELECT 
        COUNT(id) as total_reg,
        SUM(CASE WHEN status = 'attended' THEN 1 ELSE 0 END) as attended
    FROM event_registrations 
    WHERE event_id IN (SELECT event_id FROM events WHERE organizer_id = ?)
");
$stmt->bind_param("i", $organizer_id);
$stmt->execute();
$reg_data = $stmt->get_result()->fetch_assoc();
$total_registrations = $reg_data['total_reg'] ?? 0;
$total_attended = $reg_data['attended'] ?? 0;

// 5. Ratings and Feedback
$stmt = $conn->prepare("
    SELECT COUNT(*) as feedback_count, AVG(rating) as avg_rating
    FROM event_feedback 
    WHERE event_id IN (SELECT event_id FROM events WHERE organizer_id = ?)
");
$stmt->bind_param("i", $organizer_id);
$stmt->execute();
$feedback_data = $stmt->get_result()->fetch_assoc();
$avg_rating = round($feedback_data['avg_rating'] ?? 0, 1);

// 6. Registration Trend (Last 6 Months)
$stmt = $conn->prepare("
    SELECT DATE_FORMAT(registered_at, '%Y-%m') as month, COUNT(*) as count
    FROM event_registrations 
    WHERE event_id IN (SELECT event_id FROM events WHERE organizer_id = ?)
    AND registered_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
    GROUP BY month ORDER BY month
");
$stmt->bind_param("i", $organizer_id);
$stmt->execute();
$trend_res = $stmt->get_result();
$months = []; $reg_counts = [];
while($row = $trend_res->fetch_assoc()){
    $months[] = date('M Y', strtotime($row['month'] . '-01'));
    $reg_counts[] = $row['count'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Organizer Dashboard | VUCEMS</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .welcome-card { background: linear-gradient(135deg, #4e73df, #224abe); color: white; border-radius: 15px; }
        .stat-card { border-radius: 10px; border-left: 5px solid; }
        .chart-container { height: 300px; }
    </style>
</head>
<body class="bg-light">
<?php include('navbar.php'); ?>
    <div id="content">
    <div class="card welcome-card mb-4 shadow">
        <div class="card-body p-5">
            <h2 class="text-white">Welcome, <?= htmlspecialchars($name) ?>!</h2>
            <p class="text-white">Here is what's happening with your events today.</p>
        </div>
    </div>

    <div class="row">
        <div class="col-md-3">
            <div class="card stat-card shadow mb-4 border-primary">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Events</div>
                    <div class="h3 mb-0 font-weight-bold"><?= $total_events ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card shadow mb-4 border-success">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Total Registrations</div>
                    <div class="h3 mb-0 font-weight-bold"><?= $total_registrations ?></div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card stat-card shadow mb-4 border-warning">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Avg. Satisfaction</div>
                    <div class="h3 mb-0 font-weight-bold"><?= $avg_rating ?> / 5.0</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Event Status</h6>
                </div>
                <div class="card-body">
                    <div class="chart-container"><canvas id="statusChart"></canvas></div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-success">Physical vs Virtual</h6>
                </div>
                <div class="card-body">
                    <div class="chart-container"><canvas id="typeChart"></canvas></div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-info">Registration Growth</h6>
                </div>
                <div class="card-body">
                    <div class="chart-container"><canvas id="trendChart"></canvas></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Status Chart
new Chart(document.getElementById('statusChart'), {
    type: 'doughnut',
    data: {
        labels: ['Approved', 'Pending', 'Other'],
        datasets: [{
            data: [<?= $event_status['approved'] ?>, <?= $event_status['pending'] ?>, <?= $event_status['rejected'] + $event_status['cancelled'] ?>],
            backgroundColor: ['#28a745', '#ffc107', '#dc3545']
        }]
    },
    options: { maintainAspectRatio: false }
});

// Type Chart
new Chart(document.getElementById('typeChart'), {
    type: 'pie',
    data: {
        labels: ['Physical', 'Virtual'],
        datasets: [{
            data: [<?= $event_types['physical'] ?>, <?= $event_types['virtual'] ?>],
            backgroundColor: ['#4e73df', '#36b9cc']
        }]
    },
    options: { maintainAspectRatio: false }
});

// Trend Chart
new Chart(document.getElementById('trendChart'), {
    type: 'line',
    data: {
        labels: <?= json_encode($months) ?>,
        datasets: [{
            label: 'Registrations',
            data: <?= json_encode($reg_counts) ?>,
            borderColor: '#0340f8',
            fill: true,
            backgroundColor: 'red'
        }]
    },
    options: { maintainAspectRatio: false }
});
</script>
</body>
</html>

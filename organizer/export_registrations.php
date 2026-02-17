<?php
session_start();
include('config.php');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Event Organizer') {
    exit('Unauthorized');
}

$organizer_id = $_SESSION['user_id'];
$event_id = isset($_GET['event_id']) ? intval($_GET['event_id']) : 0;

// Fetch registrations
$sql = "
SELECT 
    u.first_name, u.last_name, u.email, e.title AS event_title,
    er.status, er.cancellation_reason, er.registered_at
FROM event_registrations er
INNER JOIN events e ON er.event_id = e.event_id
INNER JOIN users u ON er.user_id = u.id
WHERE e.organizer_id = ?";
if ($event_id > 0) $sql .= " AND e.event_id = $event_id";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $organizer_id);
$stmt->execute();
$result = $stmt->get_result();

// Export CSV
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="registrations.csv"');

$output = fopen('php://output', 'w');
fputcsv($output, ['Event','Student Name','Email','Status','Cancellation Reason','Registered On']);

while($row = $result->fetch_assoc()) {
    fputcsv($output, [
        $row['event_title'],
        $row['first_name'].' '.$row['last_name'],
        $row['email'],
        $row['status'],
        $row['cancellation_reason'],
        $row['registered_at']
    ]);
}
fclose($output);
exit;

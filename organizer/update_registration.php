<?php
session_start();
include('config.php');

/* ===============================
   AUTH CHECK
================================ */
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Event Organizer') {
    header("Location: ../login.php");
    exit;
}

 

/* ===============================
   INPUT
================================ */
$registration_id     = intval($_POST['registration_id']);
$status              = $_POST['status'];
$cancellation_reason = trim($_POST['cancellation_reason']);

/* ===============================
   UPDATE REGISTRATION
================================ */
$updateSql = "
UPDATE event_registrations
SET status = ?, cancellation_reason = ?
WHERE id = ?
";
$stmt = $conn->prepare($updateSql);
$stmt->bind_param("ssi", $status, $cancellation_reason, $registration_id);
$stmt->execute();
$stmt->close();

/* ===============================
   FETCH USER + EVENT DETAILS
================================ */
$infoSql = "
SELECT 
    u.email,
    u.first_name,
    u.last_name,
    e.title AS event_title
FROM event_registrations er
INNER JOIN users u  ON er.user_id = u.id
INNER JOIN events e ON er.event_id = e.event_id
WHERE er.id = ?
";
$stmt = $conn->prepare($infoSql);
$stmt->bind_param("i", $registration_id);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();
$stmt->close();

/* ===============================
   REDIRECT BACK
================================ */
header("Location: event_registrations.php");
exit;

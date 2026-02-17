<?php
session_start();
include('config.php');

// ===============================
// Authorization Check
// ===============================
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Event Organizer') {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$message = "";

// ===============================
// Fetch Categories
// ===============================
$categories = [];
$result = mysqli_query($conn, "SELECT id, name FROM event_categories ORDER BY name ASC");
while ($row = mysqli_fetch_assoc($result)) {
    $categories[] = $row;
}

// ===============================
// Handle Form Submission
// ===============================
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $category_id = !empty($_POST['category_id']) ? intval($_POST['category_id']) : NULL;
    $event_type = $_POST['event_type'];
    $venue = !empty($_POST['venue']) ? trim($_POST['venue']) : NULL;
    $meeting_link = !empty($_POST['meeting_link']) ? trim($_POST['meeting_link']) : NULL;
    $start_datetime = $_POST['start_datetime'];
    $end_datetime = $_POST['end_datetime'];
    $capacity = intval($_POST['capacity']);
    $registration_deadline = !empty($_POST['registration_deadline']) ? $_POST['registration_deadline'] : NULL;

    // ===============================
    // Validation
    // ===============================
    if ($event_type === 'physical') {
        $meeting_link = NULL;
        if (empty($venue)) {
            $message = '<div class="alert alert-danger">Venue is required for physical events.</div>';
        }
    }

    if ($event_type === 'virtual') {
        $venue = NULL;
        if (empty($meeting_link)) {
            $message = '<div class="alert alert-danger">Meeting link is required for virtual events.</div>';
        }
    }

    if (strtotime($end_datetime) <= strtotime($start_datetime)) {
        $message = '<div class="alert alert-danger">End date must be after start date.</div>';
    }

    // ===============================
    // Banner Upload
    // ===============================
    $banner_image = NULL;

    if (!empty($_FILES['banner_image']['name'])) {

        $target_dir = "../images/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        $file_extension = strtolower(pathinfo($_FILES["banner_image"]["name"], PATHINFO_EXTENSION));
        $allowed_extensions = ['jpg','jpeg','png','gif'];

        if (in_array($file_extension, $allowed_extensions)) {

            $new_filename = time() . "_" . uniqid() . "." . $file_extension;
            $target_file = $target_dir . $new_filename;

            if (move_uploaded_file($_FILES["banner_image"]["tmp_name"], $target_file)) {
                $banner_image = $new_filename;
            }
        }
    }

    // ===============================
    // Insert If No Error
    // ===============================
    if (empty($message)) {

        $stmt = mysqli_prepare($conn, "INSERT INTO events 
            (title, description, organizer_id, category_id, event_type, venue, meeting_link, start_datetime, end_datetime, capacity, registration_deadline, banner_image, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'approved')");

        mysqli_stmt_bind_param(
            $stmt,
            "ssisssssssss",
            $title,
            $description,
            $user_id,
            $category_id,
            $event_type,
            $venue,
            $meeting_link,
            $start_datetime,
            $end_datetime,
            $capacity,
            $registration_deadline,
            $banner_image
        );

        if (mysqli_stmt_execute($stmt)) {
            $message = '<div class="alert alert-success">Event added successfully and approved.</div>';
        } else {
            $message = '<div class="alert alert-danger">Failed to add event.</div>';
        }

        mysqli_stmt_close($stmt);
    }
}

mysqli_close($conn);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Event</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<?php include('navbar.php'); ?>

    <div id="content">


<div class="container mt-5">
    <div class="card mx-auto shadow" style="max-width:850px;">
        <div class="card-body">

            <h4 class="text-center mb-4">Add New Event</h4>

            <?php echo $message; ?>

            <form method="POST" enctype="multipart/form-data">

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label>Title</label>
                        <input type="text" name="title" class="form-control" required>
                    </div>

                    <div class="form-group col-md-6">
                        <label>Category</label>
                        <select name="category_id" class="form-control">
                            <option value="">Select Category</option>
                            <?php foreach($categories as $cat): ?>
                                <option value="<?= $cat['id']; ?>">
                                    <?= htmlspecialchars($cat['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" class="form-control" rows="4" required></textarea>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label>Event Type</label>
                        <select name="event_type" id="event_type" class="form-control" required>
                            <option value="physical">Physical</option>
                            <option value="virtual">Virtual</option>
                        </select>
                    </div>

                    <div class="form-group col-md-6">
                        <label>Capacity (0 = Unlimited)</label>
                        <input type="number" name="capacity" class="form-control" value="0">
                    </div>
                </div>

                <div class="form-group" id="venue_field">
                    <label>Venue</label>
                    <input type="text" name="venue" class="form-control">
                </div>

                <div class="form-group d-none" id="meeting_link_field">
                    <label>Meeting Link</label>
                    <input type="url" name="meeting_link" class="form-control">
                </div>

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label>Start Date & Time</label>
                        <input type="datetime-local" name="start_datetime" class="form-control" required>
                    </div>

                    <div class="form-group col-md-6">
                        <label>End Date & Time</label>
                        <input type="datetime-local" name="end_datetime" class="form-control" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>Registration Deadline (Optional)</label>
                    <input type="datetime-local" name="registration_deadline" class="form-control">
                </div>

                <div class="form-group">
                    <label>Banner Image (Optional)</label>
                    <input type="file" name="banner_image" class="form-control-file">
                </div>

                <div class="text-center mt-3">
                    <button class="btn btn-primary px-4">Add Event</button>
                    <a href="view_events.php" class="btn btn-secondary px-4">View Events</a>
                </div>

            </form>

        </div>
    </div>
</div>

<script>
document.getElementById("event_type").addEventListener("change", function() {
    if (this.value === "physical") {
        document.getElementById("venue_field").classList.remove("d-none");
        document.getElementById("meeting_link_field").classList.add("d-none");
    } else {
        document.getElementById("venue_field").classList.add("d-none");
        document.getElementById("meeting_link_field").classList.remove("d-none");
    }
});
</script>

</body>
</html>

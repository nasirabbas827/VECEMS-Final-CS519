<?php
session_start();
include('config.php');

// ────────────────────────────────────────────────
//  Authentication – only admin
// ────────────────────────────────────────────────
if (!isset($_SESSION["usertype"]) || $_SESSION["usertype"] !== "admin") {
    header("Location: admin_login.php");
    exit;
}

$user_id = intval($_GET['id'] ?? 0);
$message = "";
$msg_class = "";

// ────────────────────────────────────────────────
//  Handle Update Request
// ────────────────────────────────────────────────
if (isset($_POST['update_user'])) {
    $first_name = $_POST['first_name'];
    $last_name  = $_POST['last_name'];
    $email      = $_POST['email'];
    $phone      = $_POST['phone'];
    $role       = $_POST['role']; // Student, Faculty, Event Organizer
    $status     = $_POST['status']; // pending, active, suspended
    $dept       = $_POST['department'];

    $update_sql = "UPDATE users SET 
                    first_name = ?, 
                    last_name = ?, 
                    email = ?, 
                    phone = ?, 
                    role = ?, 
                    status = ?, 
                    department = ?,
                    updated_at = NOW() 
                   WHERE id = ?";
    
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param("sssssssi", $first_name, $last_name, $email, $phone, $role, $status, $dept, $user_id);
    
    if ($stmt->execute()) {
        $message = "User profile updated successfully!";
        $msg_class = "alert-success";
    } else {
        $message = "Error updating profile: " . $conn->error;
        $msg_class = "alert-danger";
    }
    $stmt->close();
}

// ────────────────────────────────────────────────
//  Fetch User Data
// ────────────────────────────────────────────────
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$user) {
    die("<div class='container mt-5 alert alert-danger'>User not found.</div>");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit User | Admin Panel</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="../css/style.css">

    <style>
        .profile-img {
            width: 130px; height: 130px;
            object-fit: cover; border-radius: 50%;
            border: 4px solid #fff; box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .card { border: none; border-radius: 15px; }
        .card-header { background: #4e73df; color: white; border-radius: 15px 15px 0 0 !important; }
    </style>
</head>
<body>

<?php include('admin_navbar.php'); ?>

<div class="container mt-5 mb-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            
            <?php if($message): ?>
                <div class="alert <?= $msg_class ?> alert-dismissible fade show">
                    <?= $message ?>
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                </div>
            <?php endif; ?>

            <div class="card shadow">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h5 class="m-0 font-weight-bold">Edit User Profile</h5>
                    <a href="admin_view_users.php" class="btn btn-sm btn-light text-primary">Back to List</a>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <div class="text-center mb-4">
                            <?php 
                                $img_path = !empty($user['profile_image']) ? "../images/".$user['profile_image'] : "../images/default.png";
                            ?>
                            <img src="<?= htmlspecialchars($img_path) ?>" alt="Profile" class="profile-img mb-2">
                            <p class="text-muted small">User ID: #<?= $user['id'] ?></p>
                        </div>

                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label class="font-weight-bold">First Name</label>
                                <input type="text" name="first_name" class="form-control" value="<?= htmlspecialchars($user['first_name']) ?>" required>
                            </div>
                            <div class="col-md-6 form-group">
                                <label class="font-weight-bold">Last Name</label>
                                <input type="text" name="last_name" class="form-control" value="<?= htmlspecialchars($user['last_name']) ?>" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label class="font-weight-bold">Email Address</label>
                                <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" required>
                            </div>
                            <div class="col-md-6 form-group">
                                <label class="font-weight-bold">Phone Number</label>
                                <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($user['phone']) ?>">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label class="font-weight-bold">Department</label>
                                <input type="text" name="department" class="form-control" value="<?= htmlspecialchars($user['department']) ?>" required>
                            </div>
                            <div class="col-md-6 form-group">
                                <label class="font-weight-bold">User Role</label>
                                <select name="role" class="form-control">
                                    <option value="Student" <?= $user['role'] == 'Student' ? 'selected' : '' ?>>Student</option>
                                    <option value="Faculty" <?= $user['role'] == 'Faculty' ? 'selected' : '' ?>>Faculty</option>
                                    <option value="Event Organizer" <?= $user['role'] == 'Event Organizer' ? 'selected' : '' ?>>Event Organizer</option>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label class="font-weight-bold">Account Status</label>
                                <select name="status" class="form-control">
                                    <option value="pending" <?= $user['status'] == 'pending' ? 'selected' : '' ?>>Pending Approval</option>
                                    <option value="active" <?= $user['status'] == 'active' ? 'selected' : '' ?>>Active</option>
                                    <option value="suspended" <?= $user['status'] == 'suspended' ? 'selected' : '' ?>>Suspended</option>
                                </select>
                            </div>
                            <div class="col-md-6 form-group">
                                <label class="font-weight-bold">Notifications</label>
                                <div class="mt-2">
                                    <span class="badge badge-<?= $user['system_notifications'] ? 'success' : 'secondary' ?>">
                                        <?= $user['system_notifications'] ? 'Enabled' : 'Disabled' ?>
                                    </span>
                                    <small class="text-muted ml-2">(Managed by user)</small>
                                </div>
                            </div>
                        </div>

                        <hr>
                        <div class="d-flex justify-content-between">
                            <div class="small text-muted">
                                <strong>Created:</strong> <?= date('M d, Y', strtotime($user['created_at'])) ?><br>
                                <strong>Last Update:</strong> <?= date('M d, Y H:i', strtotime($user['updated_at'])) ?>
                            </div>
                            <button type="submit" name="update_user" class="btn btn-primary px-4">
                                <i class="fas fa-save mr-2"></i>Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
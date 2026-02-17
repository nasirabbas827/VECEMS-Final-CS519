<?php
session_start();
require_once 'config.php';

// 1. Database Connection (using your specific VUCEMS_db)
$conn = new mysqli("localhost", "root", "", "VUCEMS_db");
if ($conn->connect_error) { die("Connection failed: " . $conn->connect_error); }

// 2. Logic: Search & Filter
$searchTerm = $_GET['search'] ?? '';
$catID = $_GET['cat'] ?? '';

$sql = "SELECT e.*, c.name as cat_name, u.first_name, u.last_name 
        FROM events e 
        LEFT JOIN event_categories c ON e.category_id = c.id 
        LEFT JOIN users u ON e.organizer_id = u.id 
        WHERE e.status = 'approved' AND e.start_datetime >= NOW()";

if ($searchTerm) $sql .= " AND (e.title LIKE '%$searchTerm%' OR e.description LIKE '%$searchTerm%')";
if ($catID) $sql .= " AND e.category_id = " . intval($catID);

$sql .= " ORDER BY e.start_datetime ASC LIMIT 9";
$events_result = $conn->query($sql);

// 3. Logic: Stats
$total_active_users = $conn->query("SELECT COUNT(*) FROM users WHERE status='active'")->fetch_row()[0];
$total_events = $conn->query("SELECT COUNT(*) FROM events WHERE status='approved'")->fetch_row()[0];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VUCEMS | Campus Event Portal</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="./css/style.css">

    
    <style>
        :root {
            --primary: #4361ee;
            --secondary: #3f37c9;
            --accent: #4cc9f0;
            --bg: #f8faff;
            --card-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
        }

        body { 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            background-color: var(--bg);
            color: #2b2d42;
        }

        /* Hero Section */
        .glass-hero {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            padding: 140px 0 100px;
            color: white;
            position: relative;
            overflow: hidden;
        }

        .search-box {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            padding: 30px;
            border-radius: 24px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 20px 50px rgba(0,0,0,0.3);
        }

        /* Event Cards */
        .event-card {
            border: none;
            border-radius: 20px;
            overflow: hidden;
            background: white;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            box-shadow: var(--card-shadow);
        }

        .event-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(67, 97, 238, 0.15);
        }

        .event-banner {
            height: 200px;
            background-size: cover;
            background-position: center;
            position: relative;
        }

        .type-badge {
            position: absolute;
            top: 15px;
            left: 15px;
            padding: 5px 15px;
            border-radius: 50px;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .badge-physical { background: #ffedd5; color: #9a3412; }
        .badge-virtual { background: #dcfce7; color: #166534; }

        .stat-pill {
            background: white;
            padding: 20px;
            border-radius: 20px;
            box-shadow: var(--card-shadow);
            border-bottom: 4px solid var(--primary);
        }

        .btn-join {
            background: var(--primary);
            color: white;
            border-radius: 12px;
            font-weight: 600;
            padding: 10px 24px;
            transition: 0.3s;
        }

        .btn-join:hover {
            background: var(--secondary);
            color: white;
            box-shadow: 0 8px 15px rgba(67, 97, 238, 0.3);
        }
        /* Footer */
        footer {
            background: #212529;
            color: #adb5bd;
            padding: 60px 0 30px;
        }

        .footer-logo {
            font-size: 1.8rem;
            font-weight: 700;
            color: white;
            margin-bottom: 20px;
        }

        .footer-logo span {
            color: var(--university-gold);
        }

        .footer-links h5 {
            color: white;
            margin-bottom: 20px;
            font-size: 1.1rem;
        }

        .footer-links ul {
            list-style: none;
            padding: 0;
        }

        .footer-links li {
            margin-bottom: 10px;
        }

        .footer-links a {
            color: #adb5bd;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .footer-links a:hover {
            color: var(--university-gold);
            padding-left: 5px;
        }

        .copyright {
            border-top: 1px solid #343a40;
            padding-top: 30px;
            margin-top: 50px;
            text-align: center;
            color: #6c757d;
            font-size: 0.9rem;
        }

    </style>
</head>
<body>

<?php include('navbar.php'); ?>

<section class="glass-hero">
    <div class="container text-center">
        <span class="badge badge-pill badge-primary mb-3 px-3 py-2">University Event Management System</span>
        <h1 class="display-3 font-weight-bold text-white mb-4">Connect. Explore. <span class="text-info">Experience.</span></h1>
        <p class="lead opacity-75 mb-5 mx-auto" style="max-width: 600px;">Join thousands of students and faculty in the most vibrant campus community portal.</p>
        
        <div class="col-lg-8 mx-auto search-box">
            <form action="index.php" method="GET" class="row no-gutters">
                <div class="col-md-9 mb-2 mb-md-0">
                    <input type="text" name="search" class="form-control form-control-lg border-0 bg-white" placeholder="What are you looking for?" value="<?= htmlspecialchars($searchTerm) ?>">
                </div>
                <div class="col-md-3 pl-md-2">
                    <button class="btn btn-info btn-lg btn-block font-weight-bold" type="submit">Discover</button>
                </div>
            </form>
        </div>
    </div>
</section>

<div class="container" style="margin-top: -40px;">
    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="stat-pill text-center">
                <h3 class="font-weight-bold"><?= $total_events ?>+</h3>
                <p class="text-muted mb-0">Upcoming Events</p>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="stat-pill text-center">
                <h3 class="font-weight-bold"><?= $total_active_users ?>+</h3>
                <p class="text-muted mb-0">Active Members</p>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="stat-pill text-center">
                <h3 class="font-weight-bold">100%</h3>
                <p class="text-muted mb-0">Campus Verified</p>
            </div>
        </div>
    </div>
</div>

<section class="py-5">
    <div class="container">
        <div class="d-flex justify-content-between align-items-end mb-5">
            <div>
                <h2 class="font-weight-bold">Latest Events</h2>
                <p class="text-muted">Handpicked experiences for you</p>
            </div>
            <div class="dropdown">
                <button class="btn btn-white shadow-sm dropdown-toggle" data-toggle="dropdown">
                    <i class="fa fa-filter mr-2"></i> Categories
                </button>
                <div class="dropdown-menu dropdown-menu-right">
                    <a class="dropdown-item" href="index.php">All Categories</a>
                    <?php 
                    $cats = $conn->query("SELECT * FROM event_categories");
                    while($c = $cats->fetch_assoc()): ?>
                        <a class="dropdown-item" href="?cat=<?= $c['id'] ?>"><?= $c['name'] ?></a>
                    <?php endwhile; ?>
                </div>
            </div>
        </div>

        <div class="row">
            <?php if ($events_result->num_rows > 0): ?>
                <?php while($e = $events_result->fetch_assoc()): ?>
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="card event-card h-100">
                            <div class="event-banner" style="background-image: url('images/<?= $e['banner_image'] ?: 'default_event.jpg' ?>');">
                                <span class="type-badge badge-<?= $e['event_type'] ?>">
                                    <?= $e['event_type'] ?>
                                </span>
                            </div>
                            <div class="card-body">
                                <small class="text-primary font-weight-bold text-uppercase"><?= $e['cat_name'] ?></small>
                                <h5 class="card-title font-weight-bold mt-1"><?= htmlspecialchars($e['title']) ?></h5>
                                
                                <div class="d-flex align-items-center text-muted small mb-2">
                                    <i class="far fa-calendar-alt mr-2"></i>
                                    <?= date('D, M d, Y', strtotime($e['start_datetime'])) ?>
                                </div>
                                
                                <div class="d-flex align-items-center text-muted small mb-3">
                                    <i class="fa fa-map-marker-alt mr-2"></i>
                                    <?= $e['event_type'] == 'physical' ? htmlspecialchars($e['venue']) : 'Virtual Link Provided' ?>
                                </div>

                                <div class="d-flex justify-content-between align-items-center mt-4">
                                    <button class="btn btn-join" onclick="viewDetails(<?= htmlspecialchars(json_encode($e)) ?>)">
                                        View Details
                                    </button>
                                    <small class="text-muted">By <?= $e['first_name'] ?></small>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-12 text-center py-5">
                    <p class="lead text-muted">No events match your search. Try a different keyword!</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<div class="modal fade" id="eventModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0" style="border-radius: 25px;">
            <div class="modal-body p-0">
                <div class="row no-gutters">
                    <div class="col-md-5 d-none d-md-block" id="modalImg" style="background-size: cover; background-position: center;"></div>
                    <div class="col-md-7 p-4">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <span id="modalCat" class="badge badge-primary mb-2"></span>
                        <h3 id="modalTitle" class="font-weight-bold mb-3"></h3>
                        <p id="modalDesc" class="text-muted" style="max-height: 150px; overflow-y: auto;"></p>
                        
                        <div class="bg-light p-3 rounded mb-3">
                            <div class="small mb-1"><i class="fa fa-clock mr-2 text-info"></i><span id="modalTime"></span></div>
                            <div class="small"><i class="fa fa-users mr-2 text-info"></i> Capacity: <span id="modalCap"></span></div>
                        </div>
                        
                        <a href="login.php" class="btn btn-info btn-block btn-lg">Login to Register</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
function viewDetails(e) {
    $('#modalTitle').text(e.title);
    $('#modalDesc').text(e.description);
    $('#modalCat').text(e.cat_name);
    $('#modalTime').text(e.start_datetime);
    $('#modalCap').text(e.capacity);
    $('#modalImg').css('background-image', 'url(images/' + (e.banner_image || 'default_event.jpg') + ')');
    $('#eventModal').modal('show');
}
</script>
<!-- Footer -->
<footer>
    <div class="container">
        <div class="row">
            <div class="col-lg-4 mb-4">
                <div class="footer-logo">VU<span>CEMS</span></div>
                <p>University Event Management System - Streamlining campus events for a connected university community.</p>
                <div class="mt-4">
                    <a href="#" class="text-light mr-3"><i class="fab fa-facebook fa-lg"></i></a>
                    <a href="#" class="text-light mr-3"><i class="fab fa-twitter fa-lg"></i></a>
                    <a href="#" class="text-light mr-3"><i class="fab fa-instagram fa-lg"></i></a>
                    <a href="#" class="text-light"><i class="fab fa-linkedin fa-lg"></i></a>
                </div>
            </div>
            
            <div class="col-lg-2 col-md-4 mb-4">
                <div class="footer-links">
                    <h5>Quick Links</h5>
                    <ul>
                        <li><a href="login.php">Home</a></li>
                        <li><a href="login.php">Events</a></li>
                        <li><a href="login.php">About</a></li>
                        <li><a href="login.php">Contact</a></li>
                    </ul>
                </div>
            </div>
            
            <div class="col-lg-2 col-md-4 mb-4">
                <div class="footer-links">
                    <h5>Resources</h5>
                    <ul>
                        <li><a href="login.php">Help Center</a></li>
                        <li><a href="login.php">FAQ</a></li>
                        <li><a href="login.php">User Guides</a></li>
                        <li><a href="login.php">API Docs</a></li>
                    </ul>
                </div>
            </div>
            
            <div class="col-lg-4 col-md-4 mb-4">
                <div class="footer-links">
                    <h5>Contact Info</h5>
                    <ul>
                        <li><i class="fas fa-map-marker-alt mr-2"></i> University Campus</li>
                        <li><i class="fas fa-phone mr-2"></i> (123) 456-7890</li>
                        <li><i class="fas fa-envelope mr-2"></i> support@VUCEMS.edu</li>
                        <li><i class="fas fa-clock mr-2"></i> Mon-Fri: 9:00 AM - 5:00 PM</li>
                    </ul>
                </div>
            </div>
        </div>
        
        <div class="copyright">
            <p>&copy; <?= date('Y') ?> University Event Management System. All rights reserved.</p>
            <p class="mb-0">
                <a href="login.php" class="text-muted mr-3">Privacy Policy</a> | 
                <a href="login.php" class="text-muted mr-3">Terms of Service</a> | 
                <a href="login.php" class="text-muted">Accessibility</a>
            </p>
        </div>
    </div>
</footer>
</body>
</html>
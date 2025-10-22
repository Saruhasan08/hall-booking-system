<?php
include 'config.php';
session_start();

if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];
?>
<!DOCTYPE html>
<html>
<head>
    <title>Dashboard - Hall Booking</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">🏢 Hall Booking System</a>
            <div class="navbar-text">
                Welcome, <?php echo $user_name; ?> | 
                <a href="logout.php" class="text-white">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h2>User Dashboard</h2>
        
        <div class="row mt-4">
            <div class="col-md-4">
                <div class="card text-white bg-primary">
                    <div class="card-body">
                        <h5 class="card-title">Book New Hall</h5>
                        <a href="book-hall.php" class="btn btn-light">Book Now</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-white bg-success">
                    <div class="card-body">
                        <h5 class="card-title">My Bookings</h5>
                        <a href="my-bookings.php" class="btn btn-light">View</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-white bg-info">
                    <div class="card-body">
                        <h5 class="card-title">Available Halls</h5>
                        <a href="halls.php" class="btn btn-light">View</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
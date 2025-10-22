<?php
include 'config.php';
session_start();

if(!isset($_SESSION['admin_id'])) {
    header("Location: admin-login.php");
    exit();
}

// Get counts for dashboard
$total_bookings = $conn->query("SELECT COUNT(*) as total FROM bookings")->fetch_assoc()['total'];
$pending_bookings = $conn->query("SELECT COUNT(*) as pending FROM bookings WHERE status='pending'")->fetch_assoc()['pending'];
$confirmed_bookings = $conn->query("SELECT COUNT(*) as confirmed FROM bookings WHERE status='confirmed'")->fetch_assoc()['confirmed'];
$total_halls = $conn->query("SELECT COUNT(*) as total FROM halls WHERE is_active=TRUE")->fetch_assoc()['total'];
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard - Hall Booking</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .stat-card {
            border-radius: 10px;
            color: white;
            padding: 20px;
            text-align: center;
            margin-bottom: 20px;
        }
        .card-1 { background: linear-gradient(45deg, #007bff, #0056b3); }
        .card-2 { background: linear-gradient(45deg, #ffc107, #e0a800); }
        .card-3 { background: linear-gradient(45deg, #28a745, #1e7e34); }
        .card-4 { background: linear-gradient(45deg, #17a2b8, #138496); }
        .action-card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }
        .action-card:hover {
            transform: translateY(-5px);
        }
        .college-type-badge {
            font-size: 0.8rem;
            padding: 5px 10px;
            border-radius: 20px;
        }
        .badge-sf {
            background-color: #dc3545;
            color: white;
        }
        .badge-aided {
            background-color: #198754;
            color: white;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-dark bg-danger">
        <div class="container">
            <a class="navbar-brand" href="admin-dashboard.php"><i class="fas fa-tachometer-alt"></i> Admin Dashboard</a>
            <div class="navbar-text">
                Welcome, <?php echo $_SESSION['admin_name']; ?> | 
                <a href="admin-logout.php" class="text-white">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h2><i class="fas fa-tachometer-alt"></i> Admin Dashboard</h2>
        
        <!-- Statistics Cards -->
        <div class="row mt-4">
            <div class="col-md-3">
                <div class="stat-card card-1">
                    <h5><i class="fas fa-calendar-check"></i> Total Bookings</h5>
                    <h2><?php echo $total_bookings; ?></h2>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card card-2">
                    <h5><i class="fas fa-clock"></i> Pending</h5>
                    <h2><?php echo $pending_bookings; ?></h2>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card card-3">
                    <h5><i class="fas fa-check-circle"></i> Confirmed</h5>
                    <h2><?php echo $confirmed_bookings; ?></h2>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card card-4">
                    <h5><i class="fas fa-building"></i> Total Halls</h5>
                    <h2><?php echo $total_halls; ?></h2>
                </div>
            </div>
        </div>

        <!-- Admin Actions -->
        <div class="row mt-4">
            <div class="col-md-4">
                <div class="card action-card">
                    <div class="card-body text-center">
                        <h5 class="card-title text-primary"><i class="fas fa-tasks"></i> Manage Bookings</h5>
                        <p class="card-text">Approve or reject booking requests from users</p>
                        <a href="admin-bookings.php" class="btn btn-primary">Manage Bookings</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card action-card">
                    <div class="card-body text-center">
                        <h5 class="card-title text-success"><i class="fas fa-plus-circle"></i> Add New Hall</h5>
                        <p class="card-text">Add a new seminar hall to the system</p>
                        <a href="add-hall.php" class="btn btn-success">Add Hall</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card action-card">
                    <div class="card-body text-center">
                        <h5 class="card-title text-warning"><i class="fas fa-building"></i> View Halls</h5>
                        <p class="card-text">See all available seminar halls</p>
                        <a href="halls.php" class="btn btn-warning">View Halls</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Stats -->
        <div class="row mt-5">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header bg-dark text-white">
                        <h5 class="mb-0"><i class="fas fa-chart-line"></i> Quick Stats</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6><i class="fas fa-users"></i> User Statistics:</h6>
                                <ul class="list-group">
                                    <?php
                                    $total_users = $conn->query("SELECT COUNT(*) as total FROM users")->fetch_assoc()['total'];
                                    $sf_users = $conn->query("SELECT COUNT(*) as sf FROM users WHERE college_type='SF'")->fetch_assoc()['sf'];
                                    $aided_users = $conn->query("SELECT COUNT(*) as aided FROM users WHERE college_type='Aided'")->fetch_assoc()['aided'];
                                    ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        Total Users
                                        <span class="badge bg-primary rounded-pill"><?php echo $total_users; ?></span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        Self Finance (SF) Users
                                        <span class="badge bg-danger rounded-pill"><?php echo $sf_users; ?></span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        Aided Users
                                        <span class="badge bg-success rounded-pill"><?php echo $aided_users; ?></span>
                                    </li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <h6><i class="fas fa-info-circle"></i> System Info:</h6>
                                <ul class="list-group">
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        Today's Date
                                        <span class="badge bg-secondary rounded-pill"><?php echo date('Y-m-d'); ?></span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        Current Time
                                        <span class="badge bg-secondary rounded-pill"><?php echo date('H:i:s'); ?></span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        System Status
                                        <span class="badge bg-success rounded-pill">Running</span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
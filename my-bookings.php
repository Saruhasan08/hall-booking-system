<?php
include 'config.php';
session_start();

if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Get user bookings
$sql = "SELECT b.*, h.name as hall_name 
        FROM bookings b 
        JOIN halls h ON b.hall_id = h.id 
        WHERE b.user_id = '$user_id' 
        ORDER BY b.created_at DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html>
<head>
    <title>My Bookings - Hall Booking</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">🏢 Hall Booking System</a>
            <div class="navbar-text">
                Welcome, <?php echo $_SESSION['user_name']; ?> | 
                <a href="logout.php" class="text-white">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h2>My Bookings</h2>
        
        <div class="card mt-4">
            <div class="card-body">
                <?php if($result->num_rows > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Hall</th>
                                    <th>Event</th>
                                    <th>Date</th>
                                    <th>Time</th>
                                    <th>Status</th>
                                    <th>Booked On</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($booking = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $booking['hall_name']; ?></td>
                                    <td><?php echo $booking['event_name']; ?></td>
                                    <td><?php echo $booking['booking_date']; ?></td>
                                    <td><?php echo $booking['start_time'] . ' - ' . $booking['end_time']; ?></td>
                                    <td>
                                        <?php 
                                        $status = $booking['status'];
                                        $badge_color = 'secondary';
                                        if($status == 'confirmed') $badge_color = 'success';
                                        if($status == 'rejected') $badge_color = 'danger';
                                        ?>
                                        <span class="badge bg-<?php echo $badge_color; ?>">
                                            <?php echo ucfirst($status); ?>
                                        </span>
                                    </td>
                                    <td><?php echo $booking['created_at']; ?></td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-center">No bookings found.</p>
                <?php endif; ?>
                
                <div class="text-center mt-3">
                    <a href="book-hall.php" class="btn btn-primary">Book New Hall</a>
                    <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
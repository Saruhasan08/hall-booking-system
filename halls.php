<?php
include 'config.php';
session_start();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Available Halls - Hall Booking</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="<?php echo isset($_SESSION['user_id']) ? 'dashboard.php' : 'index.php'; ?>">
                🏢 Hall Booking System
            </a>
            <?php if(isset($_SESSION['user_id'])): ?>
                <div class="navbar-text">
                    Welcome, <?php echo $_SESSION['user_name']; ?> | 
                    <a href="logout.php" class="text-white">Logout</a>
                </div>
            <?php endif; ?>
        </div>
    </nav>

    <div class="container mt-4">
        <h2>Available Halls</h2>
        
        <div class="row mt-4">
            <?php
            $halls_result = $conn->query("SELECT * FROM halls WHERE is_active = TRUE");
            while($hall = $halls_result->fetch_assoc()):
            ?>
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo $hall['name']; ?></h5>
                        <p class="card-text">
                            <strong>Capacity:</strong> <?php echo $hall['capacity']; ?> people<br>
                            <strong>Amenities:</strong> <?php echo $hall['amenities']; ?>
                        </p>
                        <?php if(isset($_SESSION['user_id'])): ?>
                            <a href="book-hall.php" class="btn btn-primary">Book This Hall</a>
                        <?php else: ?>
                            <a href="login.php" class="btn btn-primary">Login to Book</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
        
        <?php if(isset($_SESSION['user_id'])): ?>
            <div class="text-center mt-3">
                <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
            </div>
        <?php else: ?>
            <div class="text-center mt-3">
                <a href="index.php" class="btn btn-secondary">Back to Home</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
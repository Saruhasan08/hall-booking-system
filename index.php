<?php
session_start();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Hall Booking System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="#">🏢 Hall Booking System</a>
        </div>
    </nav>

    <div class="container mt-5">
        <h1>Welcome to Hall Booking</h1>
        <p>Book Seminar Hall, ICT 1, ICT 2, Auditorium</p>
        
        <div class="row mt-4">
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Seminar Hall</h5>
                        <p class="card-text">Capacity: 100 people</p>
                        <a href="login.php" class="btn btn-primary">Book Now</a>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">ICT Lab 1</h5>
                        <p class="card-text">Capacity: 50 computers</p>
                        <a href="login.php" class="btn btn-primary">Book Now</a>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">ICT Lab 2</h5>
                        <p class="card-text">Capacity: 40 computers</p>
                        <a href="login.php" class="btn btn-primary">Book Now</a>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Auditorium</h5>
                        <p class="card-text">Capacity: 200 people</p>
                        <a href="login.php" class="btn btn-primary">Book Now</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-4">
            <a href="login.php" class="btn btn-success">Login</a>
            <a href="register.php" class="btn btn-outline-primary">Register</a>
        </div>
    </div>
</body>
</html>
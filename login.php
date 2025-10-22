<?php
include 'config.php';
session_start();

if(isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];
    
    $sql = "SELECT * FROM users WHERE email='$email'";
    $result = $conn->query($sql);
    
    if($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if($password == $user['password']) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_role'] = $user['role'];
            
            echo "<script>alert('Login successful!'); window.location='dashboard.php';</script>";
        } else {
            echo "<script>alert('Invalid password!');</script>";
        }
    } else {
        echo "<script>alert('User not found!');</script>";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>User Login - Hall Booking</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .user-login-bg {
            background: linear-gradient(135deg, #74b9ff 0%, #0984e3 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        .login-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        .login-header {
            background: linear-gradient(45deg, #28a745, #20c997);
            border-radius: 15px 15px 0 0;
            padding: 25px;
        }
        .btn-login {
            background: linear-gradient(45deg, #28a745, #20c997);
            border: none;
            padding: 12px;
            font-weight: 600;
        }
        .btn-login:hover {
            background: linear-gradient(45deg, #218838, #1e7e34);
            transform: translateY(-2px);
            transition: all 0.3s;
        }
    </style>
</head>
<body>
    <div class="user-login-bg">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-5">
                    <div class="card login-card">
                        <div class="login-header text-center text-white">
                            <h3><i class="fas fa-user"></i> USER LOGIN</h3>
                            <p class="mb-0">Hall Booking System</p>
                        </div>
                        <div class="card-body p-5">
                            <form method="POST">
                                <div class="mb-4">
                                    <label class="form-label"><strong>Email Address</strong></label>
                                    <input type="email" name="email" class="form-control form-control-lg" placeholder="Enter your email" required>
                                </div>
                                <div class="mb-4">
                                    <label class="form-label"><strong>Password</strong></label>
                                    <input type="password" name="password" class="form-control form-control-lg" placeholder="Enter password" required>
                                </div>
                                <button type="submit" name="login" class="btn btn-login btn-lg w-100 text-white">
                                    <i class="fas fa-sign-in-alt"></i> USER LOGIN
                                </button>
                            </form>
                            
                            <div class="text-center mt-4">
                                <a href="register.php" class="text-decoration-none me-3">
                                    <i class="fas fa-user-plus"></i> Create Account
                                </a>
                                <a href="admin-login.php" class="text-decoration-none">
                                    <i class="fas fa-user-shield"></i> Admin Login
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</body>
</html>
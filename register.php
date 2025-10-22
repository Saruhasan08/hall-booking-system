<?php
include 'config.php';

if(isset($_POST['register'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $department = $_POST['department'];
    $college_type = $_POST['college_type'];
    $contact_number = $_POST['contact_number']; // Mandatory now
    
    // Gmail format validation
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "<script>alert('Please enter a valid email address!');</script>";
    } elseif (!preg_match('/@gmail\.com$/', $email)) {
        echo "<script>alert('Only Gmail addresses are allowed! Please use @gmail.com');</script>";
    } elseif (empty($contact_number)) {
        echo "<script>alert('Contact number is required!');</script>";
    } else {
        // Check if email already exists
        $check_sql = "SELECT * FROM users WHERE email='$email'";
        $check_result = $conn->query($check_sql);
        
        if($check_result->num_rows > 0) {
            echo "<script>alert('Email already exists! Please use different Gmail.');</script>";
        } else {
            // Simple password (no hashing for demo)
            $sql = "INSERT INTO users (name, email, password, department, college_type, contact_number) 
                    VALUES ('$name', '$email', '$password', '$department', '$college_type', '$contact_number')";
            
            if($conn->query($sql) === TRUE) {
                echo "<script>alert('Registration successful! Please login.'); window.location='login.php';</script>";
            } else {
                echo "<script>alert('Error: ".$conn->error."');</script>";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Register - Hall Booking</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .register-bg {
            background: linear-gradient(135deg, #74b9ff 0%, #0984e3 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        .register-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        .register-header {
            background: linear-gradient(45deg, #28a745, #20c997);
            border-radius: 15px 15px 0 0;
            padding: 25px;
        }
        .btn-register {
            background: linear-gradient(45deg, #28a745, #20c997);
            border: none;
            padding: 12px;
            font-weight: 600;
        }
        .btn-register:hover {
            background: linear-gradient(45deg, #218838, #1e7e34);
            transform: translateY(-2px);
            transition: all 0.3s;
        }
        .form-control:focus {
            border-color: #28a745;
            box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25);
        }
        .college-type-group {
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 15px;
            background: #f8f9fa;
        }
        .required-field::after {
            content: " *";
            color: red;
        }
    </style>
</head>
<body>
    <div class="register-bg">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="card register-card">
                        <div class="register-header text-center text-white">
                            <h3><i class="fas fa-user-plus"></i> USER REGISTRATION</h3>
                            <p class="mb-0">Create Your Account - College Hall Booking</p>
                        </div>
                        <div class="card-body p-5">
                            <form action="register.php" method="POST">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label required-field"><strong><i class="fas fa-user"></i> Full Name</strong></label>
                                            <input type="text" name="name" class="form-control form-control-lg" placeholder="Enter your full name" required>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label class="form-label required-field"><strong><i class="fas fa-envelope"></i> Email Address</strong></label>
                                            <input type="email" name="email" class="form-control form-control-lg" placeholder="example@gmail.com" pattern="[a-zA-Z0-9._%+-]+@gmail\.com$" title="Only Gmail addresses are allowed (@gmail.com)" required>
                                            <small class="text-muted">Only @gmail.com addresses are accepted</small>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label class="form-label required-field"><strong><i class="fas fa-lock"></i> Password</strong></label>
                                            <input type="password" name="password" class="form-control form-control-lg" placeholder="Create password" minlength="3" required>
                                            <small class="text-muted">Minimum 3 characters required</small>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label required-field"><strong><i class="fas fa-graduation-cap"></i> Department</strong></label>
                                            <select name="department" class="form-control form-control-lg" required>
                                                <option value="">Select Department</option>
                                                <option value="Master of Computer Applications">Master of Computer Applications (MCA)</option>
                                                <option value="Bachelor of Computer Applications">Bachelor of Computer Applications (BCA)</option>
                                                <option value="Bachelor of Science in Computer Science">Bachelor of Science in Computer Science (B.Sc CS)</option>
                                                <option value="Master of Science in Computer Science">Master of Science in Computer Science (M.Sc CS)</option>
                                                <option value="Bachelor of Science in Information Technology">Bachelor of Science in Information Technology (B.Sc IT)</option>
                                                <option value="Bachelor of Business Administration">Bachelor of Business Administration (BBA)</option>
                                                <option value="Master of Business Administration">Master of Business Administration (MBA)</option>
                                                <option value="Bachelor of Commerce">Bachelor of Commerce (B.Com)</option>
                                                <option value="Bachelor of Arts">Bachelor of Arts (B.A)</option>
                                                <option value="Bachelor of Science in Mathematics">Bachelor of Science in Mathematics (B.Sc Mathematics)</option>
                                                <option value="Master of Science in Mathematics">Master of Science in Mathematics (M.Sc Mathematics)</option>
                                                <option value="Bachelor of Science in Physics">Bachelor of Science in Physics (B.Sc Physics)</option>
                                                <option value="Master of Science in Physics">Master of Science in Physics (M.Sc Physics)</option>
                                                <option value="Bachelor of Science in Chemistry">Bachelor of Science in Chemistry (B.Sc Chemistry)</option>
                                                <option value="Master of Science in Chemistry">Master of Science in Chemistry (M.Sc Chemistry)</option>
                                                <option value="Other">Other Department</option>
                                            </select>
                                        </div>

                                        <!-- College Type -->
                                        <div class="mb-3">
                                            <label class="form-label required-field"><strong><i class="fas fa-university"></i> College Type</strong></label>
                                            <div class="college-type-group">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="college_type" id="sf" value="SF" required>
                                                    <label class="form-check-label" for="sf">
                                                        <strong>Self Finance (SF)</strong>
                                                    </label>
                                                </div>
                                                <div class="form-check mt-2">
                                                    <input class="form-check-input" type="radio" name="college_type" id="aided" value="Aided" required>
                                                    <label class="form-check-label" for="aided">
                                                        <strong>Aided</strong>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Contact Number (Mandatory Now) -->
                                        <div class="mb-3">
                                            <label class="form-label required-field"><strong><i class="fas fa-phone"></i> Contact Number</strong></label>
                                            <input type="text" name="contact_number" class="form-control form-control-lg" placeholder="Enter your contact number" required>
                                            <small class="text-muted">This field is required for registration</small>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle"></i> <strong>Note:</strong> All fields marked with <span style="color:red">*</span> are mandatory.
                                </div>
                                
                                <button type="submit" name="register" class="btn btn-register btn-lg w-100 text-white mt-3">
                                    <i class="fas fa-user-plus"></i> CREATE ACCOUNT
                                </button>
                            </form>
                            
                            <div class="text-center mt-4">
                                <p>Already have an account? 
                                    <a href="login.php" class="text-decoration-none fw-bold">Login here</a>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
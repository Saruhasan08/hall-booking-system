<?php
include 'config.php';
session_start();

if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get all halls
$halls_result = $conn->query("SELECT * FROM halls WHERE is_active = TRUE");

// Get already booked dates for each hall
$booked_dates = [];
$dates_result = $conn->query("SELECT hall_id, booking_date FROM bookings WHERE status IN ('confirmed', 'pending')");
while($date_row = $dates_result->fetch_assoc()) {
    $booked_dates[$date_row['hall_id']][] = $date_row['booking_date'];
}

// Get current month and year
$current_month = date('m');
$current_year = date('Y');

if(isset($_POST['book_hall'])) {
    $user_id = $_SESSION['user_id'];
    $hall_id = $_POST['hall_id'];
    $event_name = $_POST['event_name'];
    $chief_guest = $_POST['chief_guest'];
    $booking_date = $_POST['booking_date'];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];
    $student_strength = $_POST['student_strength'];
    $cordless_mic = isset($_POST['cordless_mic']) ? (int)$_POST['cordless_mic'] : 0;
    $wireless_mic = isset($_POST['wireless_mic']) ? (int)$_POST['wireless_mic'] : 0;
    $event_scale = $_POST['event_scale'];
    
    // Get user's program type (SF/Aided)
    $sf_program = 0;
    $aided_program = 0;

    $user_sql = "SELECT * FROM users WHERE id='$user_id'";
    $user_result = $conn->query($user_sql);
    if($user_result && $user_result->num_rows > 0) {
        $user_data = $user_result->fetch_assoc();
        $sf_program = $user_data['sf_program'];
        $aided_program = $user_data['aided_program'];
    }
    
    // Validate microphone count (max 5 total)
    $total_mics = $cordless_mic + $wireless_mic;
    if($total_mics > 5) {
        echo "<script>alert('Maximum 5 microphones allowed in total! You selected $total_mics microphones.');</script>";
    } else {
        // Convert AM/PM time to 24-hour format for database storage
        function convertTo24Hour($time) {
            return date("H:i", strtotime($time));
        }
        
        $start_time_24 = convertTo24Hour($start_time);
        $end_time_24 = convertTo24Hour($end_time);
        
        // Check if time is within allowed range (6:00 AM to 10:00 PM)
        $start_timestamp = strtotime($start_time_24);
        $end_timestamp = strtotime($end_time_24);
        $min_time = strtotime('06:00');
        $max_time = strtotime('22:00');
        
        if($start_timestamp < $min_time || $end_timestamp > $max_time) {
            echo "<script>alert('Booking time must be between 6:00 AM and 10:00 PM!');</script>";
        } else if($start_timestamp >= $end_timestamp) {
            echo "<script>alert('End time must be after start time!');</script>";
        } else {
            // Check if hall is available for the selected date and time
            $check_sql = "SELECT * FROM bookings WHERE hall_id = '$hall_id' AND booking_date = '$booking_date' 
                          AND ((start_time <= '$end_time_24' AND end_time >= '$start_time_24')) 
                          AND status IN ('confirmed', 'pending')";
            $check_result = $conn->query($check_sql);
            
            if($check_result->num_rows > 0) {
                echo "<script>alert('This hall is already booked for the selected date and time! Please choose different date/time.');</script>";
            } else {
                // Get hall capacity for validation
                $hall_capacity_sql = "SELECT capacity FROM halls WHERE id = '$hall_id'";
                $hall_capacity_result = $conn->query($hall_capacity_sql);
                $hall_capacity_row = $hall_capacity_result->fetch_assoc();
                $hall_capacity = $hall_capacity_row['capacity'];
                
                // Validate student strength against hall capacity
                if($student_strength > $hall_capacity) {
                    echo "<script>alert('Student strength ($student_strength) exceeds hall capacity ($hall_capacity)!');</script>";
                } else {
                    $sql = "INSERT INTO bookings (user_id, hall_id, event_name, chief_guest, booking_date, start_time, end_time, student_strength, cordless_mic, wireless_mic, event_scale, sf_program, aided_program) 
                            VALUES ('$user_id', '$hall_id', '$event_name', '$chief_guest', '$booking_date', '$start_time_24', '$end_time_24', '$student_strength', '$cordless_mic', '$wireless_mic', '$event_scale', '$sf_program', '$aided_program')";
                    
                    if($conn->query($sql) === TRUE) {
                        // Send email to user
                        $user_sql = "SELECT * FROM users WHERE id='$user_id'";
                        $user_result = $conn->query($user_sql);
                        $user = $user_result->fetch_assoc();
                        
                        $hall_sql = "SELECT * FROM halls WHERE id='$hall_id'";
                        $hall_result = $conn->query($hall_sql);
                        $hall = $hall_result->fetch_assoc();
                        
                        // Format time for email display
                        $start_time_display = date("g:i A", strtotime($start_time_24));
                        $end_time_display = date("g:i A", strtotime($end_time_24));
                        
                        // Prepare program type
                        $program_type = '';
                        if($sf_program && $aided_program) {
                            $program_type = 'Both SF & Aided Programs';
                        } elseif($sf_program) {
                            $program_type = 'SF Program';
                        } elseif($aided_program) {
                            $program_type = 'Aided Program';
                        }
                        
                        // Prepare equipment details for email
                        $equipment_details = "";
                        if($cordless_mic > 0) {
                            $equipment_details .= "- Cordless Microphones: $cordless_mic\n";
                        }
                        if($wireless_mic > 0) {
                            $equipment_details .= "- Wireless Microphones: $wireless_mic\n";
                        }
                        if(empty($equipment_details)) {
                            $equipment_details = "- No microphones requested\n";
                        }
                        
                        // Send email to user
                        $user_subject = "Booking Request Submitted - Hall Booking System";
                        $user_message = "Dear {$user['name']},\n\nYour booking request has been submitted successfully!\n\n📋 Booking Details:\n- Hall: {$hall['name']}\n- Event: $event_name\n- Chief Guest: $chief_guest\n- Event Scale: $event_scale\n- Program Type: $program_type\n- Date: $booking_date\n- Time: $start_time_display to $end_time_display\n- Student Strength: $student_strength\n- Equipment Requested:\n$equipment_details- Status: Pending Approval\n\nWe will notify you once admin approves your booking.\n\nThank you!";
                        sendRealEmail($user['email'], $user_subject, $user_message);
                        
                        // Send email to admin
                        $admin_subject = "New Booking Request - Action Required";
                        $admin_message = "Admin,\n\nNew booking request received!\n\nUser: {$user['name']} ({$user['email']})\nProgram Type: $program_type\nHall: {$hall['name']}\nEvent: $event_name\nChief Guest: $chief_guest\nEvent Scale: $event_scale\nDate: $booking_date\nTime: $start_time_display to $end_time_display\nStudent Strength: $student_strength\nEquipment Requested:\n$equipment_details\nPlease login to approve or reject this booking.\n\nAdmin Panel: http://localhost:8080/hall-booking/admin-login.php";
                        sendRealEmail('armugamsaruhasan8@gmail.com', $admin_subject, $admin_message);
                        
                        echo "<script>alert('Booking request submitted successfully! Email sent to you and admin.'); window.location='my-bookings.php';</script>";
                    } else {
                        echo "<script>alert('Booking failed. Please try again.');</script>";
                    }
                }
            }
        }
    }
}

// Real email function
function sendRealEmail($to, $subject, $message) {
    $headers = "From: hallbooking@college.com\r\n";
    $headers .= "Reply-To: hallbooking@college.com\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
    
    // Try to send real email
    if(mail($to, $subject, $message, $headers)) {
        file_put_contents('email_log.txt', "REAL EMAIL SENT TO: $to\nSUBJECT: $subject\nMESSAGE: $message\n\n", FILE_APPEND);
        return true;
    } else {
        file_put_contents('email_log.txt', "EMAIL FAILED FOR: $to\nSUBJECT: $subject\n\n", FILE_APPEND);
        return false;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Book Hall - Hall Booking</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        .main-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            margin-top: 20px;
            margin-bottom: 20px;
        }
        .nav-custom {
            background: linear-gradient(45deg, #28a745, #20c997);
            border-radius: 15px 15px 0 0;
        }
        .booking-card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .btn-book {
            background: linear-gradient(45deg, #28a745, #20c997);
            border: none;
            padding: 12px;
            font-weight: 600;
        }
        .btn-book:hover {
            background: linear-gradient(45deg, #218838, #1e7e34);
        }
        
        /* Compact Calendar Styles */
        .calendar-wrapper {
            background: white;
            border-radius: 10px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .calendar-header {
            background: linear-gradient(45deg, #007bff, #0056b3);
            color: white;
            padding: 12px;
            text-align: center;
        }
        .calendar-nav {
            background: #f8f9fa;
            padding: 8px;
            border-bottom: 1px solid #dee2e6;
        }
        .calendar-grid {
            padding: 10px;
        }
        .week-days {
            background: #e9ecef;
            font-weight: bold;
            font-size: 0.8em;
        }
        .calendar-day {
            border: 1px solid #e9ecef;
            height: 45px;
            padding: 4px;
            cursor: pointer;
            transition: all 0.2s;
            position: relative;
            font-size: 0.8em;
        }
        .calendar-day:hover {
            background-color: #f8f9fa;
        }
        .calendar-day.available {
            background-color: #d4edda;
        }
        .calendar-day.booked {
            background-color: #f8d7da;
            color: #721c24;
            cursor: not-allowed;
        }
        .calendar-day.past-date {
            background-color: #f8f9fa;
            color: #6c757d;
            cursor: not-allowed;
        }
        .calendar-day.booked:hover {
            background-color: #f8d7da;
            transform: none;
        }
        .calendar-day.past-date:hover {
            background-color: #f8f9fa;
            transform: none;
        }
        .calendar-day.selected {
            background-color: #007bff;
            color: white;
            border: 2px solid #0056b3;
        }
        .calendar-day.today {
            border: 2px solid #ffc107;
            background-color: #fff3cd;
        }
        .calendar-day.other-month {
            background-color: #f8f9fa;
            color: #6c757d;
        }
        .day-number {
            font-weight: bold;
        }
        .month-nav-btn {
            background: none;
            border: none;
            font-size: 0.9em;
            cursor: pointer;
            padding: 3px 10px;
        }
        .compact-legend {
            font-size: 0.75em;
        }
        .time-info {
            font-size: 0.8em;
            color: #6c757d;
        }
        .equipment-section {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 15px;
            margin-top: 10px;
        }
        .mic-counter {
            font-size: 0.8em;
            color: #dc3545;
            font-weight: bold;
        }
        .event-scale-section {
            background: #e7f3ff;
            border-radius: 10px;
            padding: 15px;
            margin-top: 10px;
            border-left: 4px solid #007bff;
        }
        .duration-display {
            background: #e8f5e8;
            border-radius: 8px;
            padding: 10px;
            margin-top: 10px;
            text-align: center;
            font-weight: bold;
        }
        .booking-summary {
            background: linear-gradient(45deg, #f8f9fa, #e9ecef);
            border-radius: 10px;
            padding: 15px;
            margin-top: 15px;
            border-left: 4px solid #28a745;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="main-container">
            <!-- Navigation -->
            <nav class="navbar nav-custom">
                <div class="container-fluid">
                    <a class="navbar-brand text-white" href="dashboard.php">
                        <i class="fas fa-calendar-plus"></i> BOOK A HALL
                    </a>
                    <div class="navbar-text">
                        <a href="dashboard.php" class="text-white me-3">
                            <i class="fas fa-tachometer-alt"></i> Dashboard
                        </a>
                        <a href="logout.php" class="text-white">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a>
                    </div>
                </div>
            </nav>

            <div class="container-fluid mt-4">
                <div class="row">
                    <div class="col-md-12">
                        <div class="card booking-card">
                            <div class="card-header bg-primary text-white">
                                <h4 class="mb-0"><i class="fas fa-calendar-alt"></i> Book Seminar Hall</h4>
                            </div>
                            <div class="card-body p-4">
                                <form action="book-hall.php" method="POST" id="bookingForm">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label class="form-label"><strong><i class="fas fa-building"></i> Select Hall</strong></label>
                                                <select name="hall_id" class="form-control" id="hallSelect" required>
                                                    <option value="">-- Select Hall --</option>
                                                    <?php while($hall = $halls_result->fetch_assoc()): ?>
                                                        <option value="<?php echo $hall['id']; ?>" data-capacity="<?php echo $hall['capacity']; ?>">
                                                            <?php echo $hall['name']; ?> (Capacity: <?php echo $hall['capacity']; ?>)
                                                        </option>
                                                    <?php endwhile; ?>
                                                </select>
                                            </div>
                                            
                                            <!-- Selected Date Display -->
                                            <div class="mb-3">
                                                <label class="form-label"><strong><i class="fas fa-calendar-check"></i> Selected Date</strong></label>
                                                <div class="date-display text-center p-2 bg-light rounded" id="selectedDateDisplay">
                                                    <small class="text-muted">No date selected</small>
                                                </div>
                                                <input type="hidden" name="booking_date" id="bookingDate" required>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label"><strong><i class="fas fa-users"></i> Student Strength</strong></label>
                                                <input type="number" name="student_strength" class="form-control" min="1" max="2500" placeholder="Enter number of students" required>
                                                <div class="time-info mt-1" id="capacityInfo">Maximum capacity: Please select a hall</div>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label"><strong><i class="fas fa-clock"></i> Start Time</strong></label>
                                                <select name="start_time" class="form-control" id="startTime" required>
                                                    <option value="">-- Select Start Time --</option>
                                                    <?php
                                                    // Generate time options from 6:00 AM to 9:30 PM in 30-minute intervals
                                                    for($hour = 6; $hour <= 21; $hour++) {
                                                        for($minute = 0; $minute <= 30; $minute += 30) {
                                                            if($hour == 21 && $minute > 0) break; // Stop at 9:30 PM
                                                            
                                                            $time_24 = sprintf("%02d:%02d", $hour, $minute);
                                                            $time_12 = date("g:i A", strtotime($time_24));
                                                            echo "<option value=\"$time_12\">$time_12</option>";
                                                        }
                                                    }
                                                    ?>
                                                </select>
                                                <div class="time-info mt-1">Available: 6:00 AM - 9:30 PM</div>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label"><strong><i class="fas fa-clock"></i> End Time</strong></label>
                                                <select name="end_time" class="form-control" id="endTime" required disabled>
                                                    <option value="">-- Select Start Time First --</option>
                                                </select>
                                                <div class="time-info mt-1" id="endTimeInfo">Select start time first</div>
                                            </div>
                                            
                                            <!-- Duration Display -->
                                            <div class="duration-display" id="durationDisplay" style="display: none;">
                                                <i class="fas fa-hourglass-half"></i> Duration: <span id="durationText">0 hours</span>
                                            </div>
                                            
                                            <!-- Booking Summary -->
                                            <div class="booking-summary" id="bookingSummary" style="display: none;">
                                                <h6><i class="fas fa-receipt"></i> Booking Summary</h6>
                                                <div id="summaryContent">
                                                    Please select hall, date, and time to see booking summary
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-8">
                                            <!-- Compact Calendar -->
                                            <div class="calendar-wrapper">
                                                <div class="calendar-header">
                                                    <h5 class="mb-0" id="calendarMonth">Select a Hall</h5>
                                                    <small id="calendarStatus">Please select a hall</small>
                                                </div>
                                                
                                                <div class="calendar-nav d-flex justify-content-between align-items-center">
                                                    <button type="button" class="month-nav-btn" onclick="changeMonth(-1)">
                                                        <i class="fas fa-chevron-left"></i> Prev
                                                    </button>
                                                    <button type="button" class="month-nav-btn" onclick="changeMonth(1)">
                                                        Next <i class="fas fa-chevron-right"></i>
                                                    </button>
                                                </div>
                                                
                                                <div class="calendar-grid">
                                                    <!-- Week Days Header -->
                                                    <div class="row week-days text-center">
                                                        <div class="col p-1 border">S</div>
                                                        <div class="col p-1 border">M</div>
                                                        <div class="col p-1 border">T</div>
                                                        <div class="col p-1 border">W</div>
                                                        <div class="col p-1 border">T</div>
                                                        <div class="col p-1 border">F</div>
                                                        <div class="col p-1 border">S</div>
                                                    </div>
                                                    
                                                    <!-- Calendar Days -->
                                                    <div id="calendarDays">
                                                        <div class="text-center py-4 text-muted">
                                                            <i class="fas fa-calendar-alt fa-2x mb-2"></i>
                                                            <p class="small">Select a hall to view calendar</p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <!-- Event Scale Section -->
                                            <div class="event-scale-section mt-3">
                                                <h6 class="mb-3"><i class="fas fa-layer-group"></i> Event Level</h6>
                                                
                                                <div class="mb-3">
                                                    <label class="form-label"><strong>Select Event Level</strong></label>
                                                    <select name="event_scale" class="form-control" id="eventScale" required>
                                                        <option value="">-- Select Event Level --</option>
                                                        <option value="Department Level">Department Level</option>
                                                        <option value="College Level">College Level</option>
                                                        <option value="University Level">University Level</option>
                                                        <option value="National Level">National Level</option>
                                                        <option value="International Level">International Level</option>
                                                    </select>
                                                    <div class="time-info mt-1">Choose the appropriate level for your event</div>
                                                </div>
                                            </div>
                                            
                                            <!-- Equipment Requirements Section -->
                                            <div class="equipment-section mt-3">
                                                <h6 class="mb-3"><i class="fas fa-microphone"></i> Equipment Requirements</h6>
                                                
                                                <!-- Microphone Requirements -->
                                                <div class="row mb-3">
                                                    <div class="col-md-6">
                                                        <label class="form-label"><strong>Cordless Microphones</strong></label>
                                                        <select name="cordless_mic" class="form-control mic-select">
                                                            <option value="0">0 - Not Required</option>
                                                            <option value="1">1</option>
                                                            <option value="2">2</option>
                                                            <option value="3">3</option>
                                                            <option value="4">4</option>
                                                            <option value="5">5</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label"><strong>Wireless Microphones</strong></label>
                                                        <select name="wireless_mic" class="form-control mic-select">
                                                            <option value="0">0 - Not Required</option>
                                                            <option value="1">1</option>
                                                            <option value="2">2</option>
                                                            <option value="3">3</option>
                                                            <option value="4">4</option>
                                                            <option value="5">5</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-12 mt-2">
                                                        <div class="mic-counter" id="micCounter">Total Microphones: 0/5</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="row mt-3">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label"><strong><i class="fas fa-tag"></i> Event Name</strong></label>
                                                <input type="text" name="event_name" class="form-control" placeholder="e.g., MCA Seminar, Workshop" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label"><strong><i class="fas fa-user-tie"></i> Chief Guest Name</strong></label>
                                                <input type="text" name="chief_guest" class="form-control" placeholder="Enter chief guest name (if any)">
                                                <div class="time-info">Leave blank if no chief guest</div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="text-center mt-3">
                                        <button type="submit" name="book_hall" class="btn btn-book btn-lg text-white">
                                            <i class="fas fa-paper-plane"></i> SUBMIT BOOKING
                                        </button>
                                        <a href="dashboard.php" class="btn btn-secondary btn-lg ms-2">
                                            <i class="fas fa-arrow-left"></i> BACK
                                        </a>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- Compact Legend -->
                        <div class="card mt-3">
                            <div class="card-header bg-info text-white py-2">
                                <h6 class="mb-0"><i class="fas fa-info-circle"></i> Calendar Guide</h6>
                            </div>
                            <div class="card-body py-2">
                                <div class="row text-center compact-legend">
                                    <div class="col-md-3">
                                        <div class="calendar-day available mx-auto">
                                            <div class="day-number">15</div>
                                        </div>
                                        <small>Available</small>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="calendar-day booked mx-auto">
                                            <div class="day-number">16</div>
                                        </div>
                                        <small>Booked</small>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="calendar-day past-date mx-auto">
                                            <div class="day-number">14</div>
                                        </div>
                                        <small>Past Date</small>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="calendar-day today mx-auto">
                                            <div class="day-number">Today</div>
                                        </div>
                                        <small>Today</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const bookedDates = <?php echo json_encode($booked_dates); ?>;
        
        let currentMonth = <?php echo $current_month; ?>;
        let currentYear = <?php echo $current_year; ?>;
        let selectedDate = '';
        let selectedHall = '';
        
        function generateCalendar() {
            const calendarDays = document.getElementById('calendarDays');
            const calendarMonth = document.getElementById('calendarMonth');
            const calendarStatus = document.getElementById('calendarStatus');
            
            if (!selectedHall) {
                calendarDays.innerHTML = `
                    <div class="text-center py-4 text-muted">
                        <i class="fas fa-calendar-alt fa-2x mb-2"></i>
                        <p class="small">Select a hall to view calendar</p>
                    </div>
                `;
                calendarMonth.textContent = 'Select a Hall';
                calendarStatus.textContent = 'Please select a hall';
                return;
            }
            
            const firstDay = new Date(currentYear, currentMonth - 1, 1);
            const lastDay = new Date(currentYear, currentMonth, 0);
            const daysInMonth = lastDay.getDate();
            const startingDay = firstDay.getDay();
            
            // Get today's date
            const today = new Date();
            const todayFormatted = today.toISOString().split('T')[0];
            
            const monthNames = ["January", "February", "March", "April", "May", "June",
                "July", "August", "September", "October", "November", "December"];
            
            calendarMonth.textContent = `${monthNames[currentMonth - 1]} ${currentYear}`;
            calendarStatus.textContent = 'Click green dates to select (Today & Future dates only)';
            
            let calendarHTML = '';
            let dayCount = 1;
            
            for (let i = 0; i < 6; i++) {
                calendarHTML += '<div class="row">';
                
                for (let j = 0; j < 7; j++) {
                    if (i === 0 && j < startingDay) {
                        calendarHTML += `
                            <div class="col calendar-day other-month text-muted">
                                <div class="day-number"> </div>
                            </div>
                        `;
                    } else if (dayCount > daysInMonth) {
                        calendarHTML += `
                            <div class="col calendar-day other-month text-muted">
                                <div class="day-number"> </div>
                            </div>
                        `;
                        dayCount++;
                    } else {
                        const currentDate = `${currentYear}-${currentMonth.toString().padStart(2, '0')}-${dayCount.toString().padStart(2, '0')}`;
                        const isToday = currentDate === todayFormatted;
                        
                        // Check if date is in the past
                        const currentDateObj = new Date(currentDate);
                        const isPastDate = currentDateObj < today && !isToday;
                        
                        const hallBookedDates = bookedDates[selectedHall] || [];
                        const isBooked = hallBookedDates.includes(currentDate);
                        const isSelected = currentDate === selectedDate;
                        
                        let dayClass = 'calendar-day available';
                        if (isBooked) {
                            dayClass = 'calendar-day booked';
                        } else if (isPastDate) {
                            dayClass = 'calendar-day past-date';
                        }
                        
                        if (isSelected) dayClass = 'calendar-day selected';
                        if (isToday) dayClass += ' today';
                        
                        // Only allow clicking on available future dates (not past dates)
                        const clickHandler = (isBooked || isPastDate) ? '' : `onclick="selectDate('${currentDate}')"`;
                        
                        calendarHTML += `
                            <div class="col ${dayClass}" ${clickHandler}>
                                <div class="day-number">${dayCount}</div>
                            </div>
                        `;
                        dayCount++;
                    }
                }
                
                calendarHTML += '</div>';
                if (dayCount > daysInMonth) break;
            }
            
            calendarDays.innerHTML = calendarHTML;
        }
        
        function selectDate(date) {
            selectedDate = date;
            document.getElementById('bookingDate').value = date;
            
            const dateObj = new Date(date);
            document.getElementById('selectedDateDisplay').innerHTML = `
                <strong>${dateObj.toLocaleDateString('en-US', { weekday: 'short', month: 'short', day: 'numeric' })}</strong>
            `;
            
            generateCalendar();
            updateBookingSummary();
        }
        
        function changeMonth(direction) {
            currentMonth += direction;
            
            if (currentMonth > 12) {
                currentMonth = 1;
                currentYear++;
            } else if (currentMonth < 1) {
                currentMonth = 12;
                currentYear--;
            }
            
            generateCalendar();
        }
        
        // Update capacity info when hall is selected
        function updateCapacityInfo() {
            const hallSelect = document.getElementById('hallSelect');
            const capacityInfo = document.getElementById('capacityInfo');
            const studentStrength = document.querySelector('input[name="student_strength"]');
            
            if (hallSelect.value) {
                const selectedOption = hallSelect.options[hallSelect.selectedIndex];
                const capacity = selectedOption.getAttribute('data-capacity');
                capacityInfo.textContent = `Maximum capacity: ${capacity} students`;
                studentStrength.setAttribute('max', capacity);
            } else {
                capacityInfo.textContent = 'Maximum capacity: Please select a hall';
            }
        }
        
        // Update microphone counter
        function updateMicCounter() {
            const cordlessMic = parseInt(document.querySelector('select[name="cordless_mic"]').value) || 0;
            const wirelessMic = parseInt(document.querySelector('select[name="wireless_mic"]').value) || 0;
            const totalMics = cordlessMic + wirelessMic;
            const micCounter = document.getElementById('micCounter');
            
            micCounter.textContent = `Total Microphones: ${totalMics}/5`;
            
            if (totalMics > 5) {
                micCounter.style.color = '#dc3545';
                micCounter.innerHTML += ' <i class="fas fa-exclamation-triangle"></i> Maximum exceeded!';
            } else {
                micCounter.style.color = '#28a745';
            }
        }

        // Smart End Time Selection
        document.getElementById('startTime').addEventListener('change', function() {
            const startTime = this.value;
            const endTimeSelect = document.getElementById('endTime');
            const endTimeInfo = document.getElementById('endTimeInfo');
            const durationDisplay = document.getElementById('durationDisplay');
            
            if (startTime) {
                // Enable end time dropdown
                endTimeSelect.disabled = false;
                endTimeSelect.innerHTML = '<option value="">-- Select End Time --</option>';
                
                // Parse start time
                const startDate = new Date(`2000-01-01 ${startTime}`);
                
                // All possible end times from 6:30 AM to 10:00 PM
                const allEndTimes = [
                    '6:30 AM', '7:00 AM', '7:30 AM', '8:00 AM', '8:30 AM', '9:00 AM', '9:30 AM',
                    '10:00 AM', '10:30 AM', '11:00 AM', '11:30 AM', '12:00 PM', '12:30 PM',
                    '1:00 PM', '1:30 PM', '2:00 PM', '2:30 PM', '3:00 PM', '3:30 PM',
                    '4:00 PM', '4:30 PM', '5:00 PM', '5:30 PM', '6:00 PM', '6:30 PM',
                    '7:00 PM', '7:30 PM', '8:00 PM', '8:30 PM', '9:00 PM', '9:30 PM', '10:00 PM'
                ];
                
                // Add only valid end times (after start time)
                allEndTimes.forEach(endTime => {
                    const endDate = new Date(`2000-01-01 ${endTime}`);
                    if (endDate > startDate) {
                        const option = document.createElement('option');
                        option.value = endTime;
                        option.textContent = endTime;
                        endTimeSelect.appendChild(option);
                    }
                });
                
                endTimeInfo.textContent = `Available end times after ${startTime}`;
                durationDisplay.style.display = 'block';
                updateDuration();
                updateBookingSummary();
            } else {
                endTimeSelect.disabled = true;
                endTimeSelect.innerHTML = '<option value="">-- Select Start Time First --</option>';
                endTimeInfo.textContent = 'Select start time first';
                durationDisplay.style.display = 'none';
                updateBookingSummary();
            }
        });

        // Update duration calculation
        function updateDuration() {
            const startTime = document.getElementById('startTime').value;
            const endTime = document.getElementById('endTime').value;
            const durationText = document.getElementById('durationText');
            
            if (startTime && endTime) {
                const start = new Date(`2000-01-01 ${startTime}`);
                const end = new Date(`2000-01-01 ${endTime}`);
                const duration = (end - start) / (1000 * 60 * 60); // hours
                
                if (duration > 0) {
                    durationText.textContent = `${duration.toFixed(1)} hours`;
                }
            }
        }

        // Update booking summary
        function updateBookingSummary() {
            const summary = document.getElementById('bookingSummary');
            const summaryContent = document.getElementById('summaryContent');
            const hallSelect = document.getElementById('hallSelect');
            const selectedDate = document.getElementById('bookingDate').value;
            const startTime = document.getElementById('startTime').value;
            const endTime = document.getElementById('endTime').value;
            const studentStrength = document.querySelector('input[name="student_strength"]').value;
            
            if (hallSelect.value && selectedDate && startTime && endTime) {
                const hallName = hallSelect.options[hallSelect.selectedIndex].text;
                const hallCapacity = hallSelect.options[hallSelect.selectedIndex].getAttribute('data-capacity');
                
                // Calculate duration
                const start = new Date(`2000-01-01 ${startTime}`);
                const end = new Date(`2000-01-01 ${endTime}`);
                const duration = (end - start) / (1000 * 60 * 60);
                
                summaryContent.innerHTML = `
                    <div class="row">
                        <div class="col-6"><strong>Hall:</strong></div>
                        <div class="col-6">${hallName}</div>
                    </div>
                    <div class="row">
                        <div class="col-6"><strong>Date:</strong></div>
                        <div class="col-6">${selectedDate}</div>
                    </div>
                    <div class="row">
                        <div class="col-6"><strong>Time:</strong></div>
                        <div class="col-6">${startTime} - ${endTime}</div>
                    </div>
                    <div class="row">
                        <div class="col-6"><strong>Duration:</strong></div>
                        <div class="col-6">${duration.toFixed(1)} hours</div>
                    </div>
                    <div class="row">
                        <div class="col-6"><strong>Students:</strong></div>
                        <div class="col-6">${studentStrength || '0'} / ${hallCapacity}</div>
                    </div>
                `;
                summary.style.display = 'block';
            } else {
                summary.style.display = 'none';
            }
        }

        // Form validation
        document.getElementById('bookingForm').addEventListener('submit', function(e) {
            const startTime = document.getElementById('startTime').value;
            const endTime = document.getElementById('endTime').value;
            
            if (startTime && endTime) {
                const start = new Date(`2000-01-01 ${startTime}`);
                const end = new Date(`2000-01-01 ${endTime}`);
                
                if (end <= start) {
                    alert('End time must be after start time!');
                    e.preventDefault();
                    return false;
                }
                
                const duration = (end - start) / (1000 * 60); // duration in minutes
                if (duration < 30) {
                    alert('Minimum booking duration is 30 minutes!');
                    e.preventDefault();
                    return false;
                }
                
                // Check if time is within allowed range (6:00 AM to 10:00 PM)
                const minTime = new Date('2000-01-01 6:00 AM');
                const maxTime = new Date('2000-01-01 10:00 PM');
                
                if (start < minTime || end > maxTime) {
                    alert('Booking time must be between 6:00 AM and 10:00 PM!');
                    e.preventDefault();
                    return false;
                }
            }
        });
        
        // Event listeners for real-time updates
        document.getElementById('endTime').addEventListener('change', function() {
            updateDuration();
            updateBookingSummary();
        });
        
        document.querySelector('input[name="student_strength"]').addEventListener('input', function() {
            updateBookingSummary();
        });
        
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('hallSelect').addEventListener('change', function() {
                selectedHall = this.value;
                updateCapacityInfo();
                generateCalendar();
                updateBookingSummary();
            });
            
            // Add event listeners for microphone selects
            document.querySelectorAll('.mic-select').forEach(select => {
                select.addEventListener('change', updateMicCounter);
            });
            
            updateCapacityInfo();
            generateCalendar();
        });
    </script>
</body>
</html>
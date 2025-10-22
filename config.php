<?php
// Database Configuration
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "hall_booking";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset to utf8
$conn->set_charset("utf8");

// Email Function - Works in XAMPP and Real Server
function sendBookingEmail($booking_data, $action, $user_email, $user_name) { 
    $subject = ""; 
    $message = ""; 
    
    // Email content
    switch($action) { 
        case 'approved': 
            $subject = "✅ Hall Booking APPROVED - College System"; 
            $message = "Dear $user_name,\n\nYour booking has been APPROVED!\n\n📅 BOOKING DETAILS:\n• Hall: {$booking_data['hall_name']}\n• Date: {$booking_data['booking_date']}\n• Time: {$booking_data['start_time']} to {$booking_data['end_time']}\n• Purpose: {$booking_data['purpose']}\n• Event Scale: {$booking_data['event_scale']}\n• Chief Guest: " . (!empty($booking_data['chief_guest']) ? $booking_data['chief_guest'] : 'Not specified') . "\n\n📍 Please arrive 15 minutes before your scheduled time.\n📱 Carry your college ID for verification.\n\nBest regards,\nCollege Administration"; 
            break; 
        case 'rejected': 
            $subject = "❌ Hall Booking REJECTED - College System"; 
            $message = "Dear $user_name,\n\nYour booking request has been REJECTED.\n\n📅 BOOKING DETAILS:\n• Hall: {$booking_data['hall_name']}\n• Date: {$booking_data['booking_date']}\n• Time: {$booking_data['start_time']} to {$booking_data['end_time']}\n• Purpose: {$booking_data['purpose']}\n\nThis could be due to:\n• Scheduling conflicts\n• Maintenance work\n• Administrative reasons\n\nPlease contact administration office for more details.\n\nBest regards,\nCollege Administration"; 
            break; 
        case 'cancelled': 
            $subject = "🗑️ Hall Booking CANCELLED - College System"; 
            $message = "Dear $user_name,\n\nYour booking has been CANCELLED by administration.\n\n📅 BOOKING DETAILS:\n• Hall: {$booking_data['hall_name']}\n• Date: {$booking_data['booking_date']}\n• Time: {$booking_data['start_time']} to {$booking_data['end_time']}\n\nPossible reasons:\n• Emergency maintenance\n• Special college event\n• Administrative requirements\n\nPlease contact administration for further assistance.\n\nBest regards,\nCollege Administration"; 
            break; 
    }
    
    // Create detailed log entry
    $email_log = "
    ==================================
    📧 EMAIL NOTIFICATION - $action
    ==================================
    🕒 TIME: " . date('Y-m-d H:i:s') . "
    👤 TO: $user_name ($user_email)
    🏛️ HALL: {$booking_data['hall_name']}
    📅 DATE: {$booking_data['booking_date']}
    ⏰ TIME: {$booking_data['start_time']} to {$booking_data['end_time']}
    📋 PURPOSE: {$booking_data['purpose']}
    📊 SCALE: {$booking_data['event_scale']}
    👨‍💼 CHIEF GUEST: " . (!empty($booking_data['chief_guest']) ? $booking_data['chief_guest'] : 'Not specified') . "
    ==================================\n\n
    ";
    
    // Save to file
    file_put_contents('email_log.txt', $email_log, FILE_APPEND);
    
    // On XAMPP, return true to continue process
    // On real server, uncomment below line for real emails
    // return mail($user_email, $subject, $message, $headers);
    
    return true;
}

// Database updates
$update_queries = [
    "ALTER TABLE bookings ADD COLUMN IF NOT EXISTS purpose VARCHAR(255) DEFAULT 'Class/Event'",
    "ALTER TABLE bookings ADD COLUMN IF NOT EXISTS created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP",
    "ALTER TABLE bookings ADD COLUMN IF NOT EXISTS event_scale ENUM('Small', 'Medium', 'Large') DEFAULT 'Small'",
    "ALTER TABLE bookings ADD COLUMN IF NOT EXISTS chief_guest VARCHAR(255) DEFAULT ''"
];

foreach($update_queries as $query) {
    $conn->query($query);
}
?>
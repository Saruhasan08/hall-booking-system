<?php
function sendRealEmail($to_email, $subject, $message) {
    $headers = "From: hallbooking@yourcollege.com\r\n";
    $headers .= "Reply-To: hallbooking@yourcollege.com\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
    
    // Try to send real email
    if(mail($to_email, $subject, $message, $headers)) {
        // Also save to log
        file_put_contents('email_log.txt', "REAL EMAIL SENT TO: $to_email\nSUBJECT: $subject\n\n", FILE_APPEND);
        return true;
    } else {
        // Fallback to log
        file_put_contents('email_log.txt', "EMAIL FAILED FOR: $to_email\nSUBJECT: $subject\n\n", FILE_APPEND);
        return false;
    }
}

function sendBookingConfirmation($user_email, $user_name, $booking_details) {
    $subject = "🎉 Booking Confirmed - Hall Booking System";
    $message = "
Dear $user_name,

🎊 Your booking has been CONFIRMED!

📋 Booking Details:
🏢 Hall: {$booking_details['hall_name']}
📅 Event: {$booking_details['event_name']}
📝 Description: {$booking_details['event_description']}
📅 Date: {$booking_details['booking_date']}
⏰ Time: {$booking_details['start_time']} to {$booking_details['end_time']}
✅ Status: CONFIRMED

Thank you for using our Hall Booking System.

Best regards,
College Administration
Hall Booking System
";

    return sendRealEmail($user_email, $subject, $message);
}

function sendBookingStatusUpdate($user_email, $user_name, $booking_details, $status) {
    $icon = "📋";
    if($status == 'REJECTED') $icon = "❌";
    if($status == 'CANCELLED') $icon = "⚠️";
    
    $subject = "$icon Booking $status - Hall Booking System";
    $message = "
Dear $user_name,

$icon Your booking has been: $status

📋 Booking Details:
🏢 Hall: {$booking_details['hall_name']}
📅 Event: {$booking_details['event_name']}
📝 Description: {$booking_details['event_description']}
📅 Date: {$booking_details['booking_date']}
⏰ Time: {$booking_details['start_time']} to {$booking_details['end_time']}
📊 Status: $status

If you have any questions, please contact administration.

Best regards,
College Administration
Hall Booking System
";

    return sendRealEmail($user_email, $subject, $message);
}

function sendNewBookingNotification($admin_email, $booking_details) {
    $subject = "📋 New Booking Request - Action Required";
    $message = "
ADMIN NOTIFICATION

📋 New booking request received!

👤 User: {$booking_details['user_name']}
📧 Email: {$booking_details['email']}
🏢 Hall: {$booking_details['hall_name']}
📅 Event: {$booking_details['event_name']}
📝 Description: {$booking_details['event_description']}
📅 Date: {$booking_details['booking_date']}
⏰ Time: {$booking_details['start_time']} to {$booking_details['end_time']}

Please login to admin panel to approve or reject this booking.

Admin Panel: http://localhost:8080/hall-booking/admin-login.php

Best regards,
Hall Booking System
";

    return sendRealEmail($admin_email, $subject, $message);
}
?>
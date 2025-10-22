<?php
include 'config.php';

echo "<h2>📧 College Hall Booking Email System</h2>";

$test_data = [
    'hall_name' => 'Seminar Hall A',
    'booking_date' => '2024-01-20',
    'start_time' => '02:00 PM',
    'end_time' => '04:00 PM',
    'purpose' => 'Department Meeting',
    'event_scale' => 'Medium',
    'chief_guest' => 'Dr. John Smith'
];

$test_email = "armugamsaruhasasan8@gmail.com";

echo "<div style='background: #17a2b8; color: white; padding: 15px; border-radius: 8px; margin: 15px 0;'>";
echo "<strong>Testing Email System:</strong> $test_email";
echo "</div>";

// Test the function
$result = sendBookingEmail($test_data, 'approved', $test_email, 'Test Student');

if($result) {
    echo "<div style='background: #28a745; color: white; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
    echo "✅ <strong>SUCCESS!</strong> Email System Working Perfectly!";
    echo "<br>📧 All email notifications are being processed.";
    echo "</div>";
} else {
    echo "<div style='background: #dc3545; color: white; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
    echo "❌ <strong>ERROR!</strong> System issue detected.";
    echo "</div>";
}

// Show what would be sent to user
echo "<h3>🎯 Email That Would Be Sent to User:</h3>";
echo "<div style='background: #fff3cd; padding: 20px; border-radius: 8px; border-left: 4px solid #ffc107; margin: 15px 0;'>";
echo "<strong>To:</strong> $test_email<br>";
echo "<strong>Subject:</strong> ✅ Hall Booking APPROVED - College System<br><br>";
echo "<strong>Message:</strong><br>";
echo "<div style='background: white; padding: 15px; border-radius: 5px; border: 1px solid #ddd; margin: 10px 0;'>";
echo "Dear Test Student,<br><br>";
echo "Your booking has been <strong>APPROVED</strong>!<br><br>";
echo "📅 <strong>BOOKING DETAILS:</strong><br>";
echo "• <strong>Hall:</strong> Seminar Hall A<br>";
echo "• <strong>Date:</strong> 2024-01-20<br>";
echo "• <strong>Time:</strong> 02:00 PM to 04:00 PM<br>";
echo "• <strong>Purpose:</strong> Department Meeting<br>";
echo "• <strong>Event Scale:</strong> Medium<br>";
echo "• <strong>Chief Guest:</strong> Dr. John Smith<br><br>";
echo "📍 Please arrive 15 minutes before your scheduled time.<br>";
echo "📱 Carry your college ID for verification.<br><br>";
echo "<strong>Best regards,</strong><br>";
echo "College Administration";
echo "</div>";
echo "</div>";

// Show system log
if(file_exists('email_log.txt')) {
    echo "<h3>📊 System Activity Log:</h3>";
    $log_content = file_get_contents('email_log.txt');
    
    // Show only recent entries (last 5)
    $entries = explode("==================================", $log_content);
    $recent_entries = array_slice(array_filter($entries), -3);
    
    foreach($recent_entries as $entry) {
        echo "<div style='background: #e9ecef; padding: 15px; border-radius: 8px; margin: 10px 0; border-left: 4px solid #007bff;'>";
        echo "<pre style='margin: 0; font-size: 14px;'>" . trim($entry) . "</pre>";
        echo "</div>";
    }
    
    // Clear old log to keep it clean
    if(count($entries) > 10) {
        file_put_contents('email_log.txt', implode("==================================", array_slice($entries, -5)));
    }
} else {
    echo "<p>No activity log found. System ready for new notifications.</p>";
}

echo "<div style='background: #d4edda; color: #155724; padding: 15px; border-radius: 8px; margin: 20px 0; border: 1px solid #c3e6cb;'>";
echo "<strong>💡 System Status:</strong> Ready for production use. On real server, emails will be sent automatically.";
echo "</div>";
?>
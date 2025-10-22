<?php
include 'config.php';

echo "<h3>Database Setup...</h3>";

$queries = [
    "ALTER TABLE bookings ADD COLUMN event_scale VARCHAR(100)",
    "ALTER TABLE bookings ADD COLUMN chief_guest VARCHAR(255)"
];

foreach($queries as $query) {
    if($conn->query($query)) {
        echo "<p style='color: green;'>✓ Success: $query</p>";
    } else {
        echo "<p style='color: red;'>✗ Error: " . $conn->error . "</p>";
    }
}

echo "<h3>Setup Completed!</h3>";
echo "<a href='book-hall.php'>Go to Booking</a>";
?>
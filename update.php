<?php
include 'config.php';

echo "<h3>Updating Database...</h3>";

// Add purpose column
$sql1 = "ALTER TABLE bookings ADD COLUMN purpose VARCHAR(255) DEFAULT 'Class/Event'";
if($conn->query($sql1) === TRUE) {
    echo "✅ Purpose column added<br>";
} else {
    echo "ℹ️ Purpose column already exists<br>";
}

// Add created_at column  
$sql2 = "ALTER TABLE bookings ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP";
if($conn->query($sql2) === TRUE) {
    echo "✅ Created_at column added<br>";
} else {
    echo "ℹ️ Created_at column already exists<br>";
}

echo "<h3>✅ Database Update Complete!</h3>";
echo "<a href='admin-bookings.php'>Go to Admin Bookings</a>";
?>
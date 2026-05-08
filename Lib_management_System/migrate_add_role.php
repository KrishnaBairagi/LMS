<?php
$conn = new mysqli("localhost", "root", "", "library_pro");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Add role column if it doesn't exist
$alter_sql = "ALTER TABLE users ADD COLUMN role ENUM('admin','user') DEFAULT 'user'";

if ($conn->query($alter_sql) === TRUE) {
    echo "✅ Role column added successfully!<br>";
    echo '<a href="dashboard.php">Go back to dashboard</a>';
} else {
    if (strpos($conn->error, "Duplicate column") !== false) {
        echo "✅ Role column already exists.<br>";
        echo '<a href="dashboard.php">Go back to dashboard</a>';
    } else {
        echo "❌ Error: " . $conn->error . "<br>";
        echo '<a href="dashboard.php">Go back to dashboard</a>';
    }
}

$conn->close();
?>

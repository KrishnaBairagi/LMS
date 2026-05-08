<?php
$conn = new mysqli("localhost", "root", "", "library_pro");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "Checking users table structure...<br>";

// Check if role column exists
$result = $conn->query("SHOW COLUMNS FROM users LIKE 'role'");

if ($result->num_rows === 0) {
    echo "❌ Role column does NOT exist. Adding it now...<br>";
    
    $alter_sql = "ALTER TABLE users ADD COLUMN role ENUM('admin','user') DEFAULT 'user'";
    if ($conn->query($alter_sql)) {
        echo "✅ Role column added successfully!<br>";
    } else {
        echo "❌ Error adding role column: " . $conn->error . "<br>";
    }
} else {
    echo "✅ Role column already exists!<br>";
}

// Show current table structure
echo "<br>Current users table structure:<br>";
$result = $conn->query("SHOW COLUMNS FROM users");
while ($row = $result->fetch_assoc()) {
    echo "- " . $row['Field'] . " (" . $row['Type'] . ")<br>";
}

$conn->close();
?>

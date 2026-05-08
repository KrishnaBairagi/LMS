<?php
// Simple script to add missing columns
$conn = new mysqli("localhost", "root", "", "library_pro");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "Testing connection... OK\n";

// Get current columns
$result = $conn->query("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME='users'");
$columns = [];
while ($row = $result->fetch_assoc()) {
    $columns[] = $row['COLUMN_NAME'];
    echo "Column: " . $row['COLUMN_NAME'] . "\n";
}

echo "\nChecking if role exists: " . (in_array('role', $columns) ? "YES" : "NO") . "\n";
echo "Checking if created_at exists: " . (in_array('created_at', $columns) ? "YES" : "NO") . "\n";

// Add missing columns
if (!in_array('role', $columns)) {
    echo "\nAdding role column...\n";
    if ($conn->query("ALTER TABLE users ADD COLUMN role ENUM('admin','user') DEFAULT 'user'")) {
        echo "SUCCESS: role column added\n";
    } else {
        echo "FAIL: " . $conn->error . "\n";
    }
}

if (!in_array('created_at', $columns)) {
    echo "\nAdding created_at column...\n";
    if ($conn->query("ALTER TABLE users ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP")) {
        echo "SUCCESS: created_at column added\n";
    } else {
        echo "FAIL: " . $conn->error . "\n";
    }
}

// Final check
$final = $conn->query("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME='users' AND COLUMN_NAME IN ('role', 'created_at')");
echo "\nFinal check - columns found: " . $final->num_rows . "\n";

$conn->close();
?>

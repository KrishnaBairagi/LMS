<?php
include 'config/db.php';

echo "<h1>Nuclear Database Repair - Rebuilding Users Table</h1>";
echo "<p>This will completely delete and rebuild the users table.</p>";

// Confirm by checking for ?confirm=yes
if (!isset($_GET['confirm']) || $_GET['confirm'] !== 'yes') {
    echo "<p><strong>⚠️ WARNING: This will delete all data in the users table!</strong></p>";
    echo "<p><a href='nuke_table.php?confirm=yes' style='background-color: red; color: white; padding: 10px 15px; border-radius: 5px; text-decoration: none; font-weight: bold;'>Click to Confirm and Rebuild</a></p>";
    echo "<p><a href='dashboard.php'>Cancel</a></p>";
    exit;
}

echo "<h2>Step 1: Dropping old users table...</h2>";
if ($conn->query("DROP TABLE IF EXISTS users")) {
    echo "<p style='color: green;'>✓ Old table dropped</p>";
} else {
    echo "<p style='color: red;'>✗ Error: " . $conn->error . "</p>";
}

echo "<h2>Step 2: Creating new users table...</h2>";
$createSQL = "CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin','user') DEFAULT 'user' NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

if ($conn->query($createSQL)) {
    echo "<p style='color: green;'>✓ New table created successfully</p>";
} else {
    echo "<p style='color: red;'>✗ Error: " . $conn->error . "</p>";
    exit;
}

echo "<h2>Step 3: Verifying table structure...</h2>";
$columns = $conn->query("SHOW COLUMNS FROM users");
$columnInfo = [];
echo "<ul>";
while ($col = $columns->fetch_assoc()) {
    $columnInfo[] = $col['Field'];
    echo "<li>" . $col['Field'] . " (" . $col['Type'] . ")</li>";
}
echo "</ul>";

$hasRole = in_array('role', $columnInfo);
$hasCreatedAt = in_array('created_at', $columnInfo);

echo "<h2>Step 4: Column Verification</h2>";
echo "<p>role column: " . ($hasRole ? "<span style='color: green;'>✓ Exists</span>" : "<span style='color: red;'>✗ Missing</span>") . "</p>";
echo "<p>created_at column: " . ($hasCreatedAt ? "<span style='color: green;'>✓ Exists</span>" : "<span style='color: red;'>✗ Missing</span>") . "</p>";

echo "<h2>Step 5: Testing query from users.php</h2>";
if ($hasRole && $hasCreatedAt) {
    $testQuery = "SELECT u.id, u.name, u.email, u.role, u.created_at, COUNT(CASE WHEN ib.status='issued' THEN 1 END) as issued_books FROM users u LEFT JOIN issued_books ib ON u.id=ib.user_id GROUP BY u.id";
    $result = $conn->query($testQuery);
    if ($result) {
        echo "<p style='color: green;'>✓ Query works!</p>";
    } else {
        echo "<p style='color: red;'>✗ Query failed: " . $conn->error . "</p>";
    }
} else {
    echo "<p style='color: red;'>✗ Cannot test - columns missing</p>";
}

// Summary
echo "<h2 style='margin-top: 30px;'>SUMMARY</h2>";
if ($hasRole && $hasCreatedAt) {
    echo "<div style='background-color: #2dd4bf; color: white; padding: 20px; border-radius: 5px; font-size: 18px;'>";
    echo "<strong>✓✓✓ SUCCESS! Database is now properly configured.</strong><br><br>";
    echo "<p>The users table has been rebuilt with all required columns.</p>";
    echo "<p><a href='login.php' style='color: white; text-decoration: none; background-color: #6c84ff; padding: 10px 15px; border-radius: 3px; display: inline-block; margin-top: 10px;'>Go to Login</a></p>";
    echo "</div>";
} else {
    echo "<div style='background-color: #ff5860; color: white; padding: 20px; border-radius: 5px; font-size: 18px;'>";
    echo "<strong>✗✗✗ FAILED! Rebuild did not complete properly.</strong>";
    echo "</div>";
}

$conn->close();
?>

<?php
// Quick database fix page
$host = "localhost";
$user = "root";
$pass = "NewPassword123";
$dbname = "library_pro";

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Connection Error: " . $conn->connect_error);
}

// Drop and recreate users table with correct schema
$drop = $conn->query("DROP TABLE IF EXISTS users");
if (!$drop) {
    echo "Error dropping table: " . $conn->error;
    exit();
}

$create = $conn->query("CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100),
    email VARCHAR(100) UNIQUE,
    password VARCHAR(255),
    role ENUM('admin','user') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

if (!$create) {
    echo "Error creating table: " . $conn->error;
    exit();
}

// Verify it worked
$check = $conn->query("SHOW COLUMNS FROM users");
$columns = [];
while ($col = $check->fetch_assoc()) {
    $columns[] = $col['Field'];
}

$conn->close();

if (in_array('role', $columns) && in_array('created_at', $columns)) {
    echo "<h1 style='color: green;'>✓ Database Fixed!</h1>";
    echo "<p>The users table has been recreated with the correct schema.</p>";
    echo "<p>Columns found: " . implode(", ", $columns) . "</p>";
    echo "<p><a href='login.php'>Click here to go to Login page</a></p>";
} else {
    echo "<h1 style='color: red;'>✗ Failed to fix database</h1>";
    echo "<p>Columns found: " . implode(", ", $columns) . "</p>";
}
?>

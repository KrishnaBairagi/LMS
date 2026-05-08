<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$host = "localhost";
$user = "root";
$pass = "NewPassword123";
$dbname = "library_pro";

echo "=== Database Reconstruction ===\n\n";

// Step 1: Connect to MySQL
echo "Step 1: Connecting to MySQL...\n";
$conn = new mysqli($host, $user, $pass);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error . "\n");
}
echo "✓ Connected\n\n";

// Step 2: Select database
echo "Step 2: Selecting database '$dbname'...\n";
if (!$conn->select_db($dbname)) {
    echo "Database doesn't exist, creating it...\n";
    $conn->query("CREATE DATABASE IF NOT EXISTS $dbname");
    $conn->select_db($dbname);
}
echo "✓ Database selected\n\n";

// Step 3: Check and drop existing users table
echo "Step 3: Checking existing users table...\n";
$tableExists = $conn->query("SHOW TABLES LIKE 'users'");
if ($tableExists && $tableExists->num_rows > 0) {
    echo "✓ Users table exists, dropping it...\n";
    if ($conn->query("DROP TABLE users")) {
        echo "✓ Table dropped\n";
    } else {
        echo "✗ Error dropping table: " . $conn->error . "\n";
    }
} else {
    echo "✓ No existing users table\n";
}
echo "\n";

// Step 4: Create new users table with correct schema
echo "Step 4: Creating users table with correct schema...\n";
$createSQL = "CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100),
    email VARCHAR(100) UNIQUE,
    password VARCHAR(255),
    role ENUM('admin','user') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($createSQL)) {
    echo "✓ Table created successfully\n";
} else {
    die("✗ Error creating table: " . $conn->error . "\n");
}
echo "\n";

// Step 5: Verify the table structure
echo "Step 5: Verifying table structure...\n";
$columns = $conn->query("SHOW COLUMNS FROM users");
$hasRole = false;
$hasCreatedAt = false;

echo "Columns in users table:\n";
while ($col = $columns->fetch_assoc()) {
    $type = $col['Type'];
    echo "  - " . $col['Field'] . " (" . $type . ")\n";
    if ($col['Field'] === 'role') $hasRole = true;
    if ($col['Field'] === 'created_at') $hasCreatedAt = true;
}
echo "\n";

// Step 6: Test the problematic query
echo "Step 6: Testing the problematic query from users.php line 101...\n";
$testQuery = "SELECT u.id, u.name, u.email, u.role, u.created_at,
       COUNT(CASE WHEN ib.status='issued' THEN 1 END) as issued_books
FROM users u
LEFT JOIN issued_books ib ON u.id=ib.user_id
GROUP BY u.id
ORDER BY u.created_at DESC";

$result = $conn->query($testQuery);
if ($result) {
    echo "✓ Query executed successfully!\n";
    echo "Rows returned: " . $result->num_rows . "\n";
} else {
    echo "✗ Query failed: " . $conn->error . "\n";
}
echo "\n";

// Final result
echo "=== SUMMARY ===\n";
if ($hasRole && $hasCreatedAt && $result) {
    echo "✓✓✓ SUCCESS! Database is now properly configured.\n";
    echo "✓ role column exists\n";
    echo "✓ created_at column exists\n";
    echo "✓ Test query works\n\n";
    echo "You can now access:\n";
    echo "- Login: http://localhost/Lib_management_System/Lib_management_System/login.php\n";
    echo "- Users: http://localhost/Lib_management_System/Lib_management_System/users.php\n";
    echo "- Dashboard: http://localhost/Lib_management_System/Lib_management_System/dashboard.php\n";
} else {
    echo "✗✗✗ FAILED! Issues found:\n";
    if (!$hasRole) echo "✗ role column missing\n";
    if (!$hasCreatedAt) echo "✗ created_at column missing\n";
    if (!$result) echo "✗ Test query failed\n";
}

$conn->close();
?>

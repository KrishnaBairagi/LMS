<?php
include 'config/db.php';

// Test if basic connection works
echo "Connection test: ";
if ($conn && !$conn->connect_error) {
    echo "✓ OK\n";
} else {
    echo "✗ FAILED\n";
    exit(1);
}

// Test if table exists
echo "Table check: ";
$tableCheck = $conn->query("SHOW TABLES LIKE 'users'");
if ($tableCheck && $tableCheck->num_rows > 0) {
    echo "✓ Exists\n";
} else {
    echo "✗ NOT FOUND\n";
    exit(1);
}

// Test if role column exists
echo "Role column: ";
$roleCheck = $conn->query("SHOW COLUMNS FROM users LIKE 'role'");
if ($roleCheck && $roleCheck->num_rows > 0) {
    echo "✓ Exists\n";
} else {
    echo "✗ MISSING\n";
}

// Test if created_at column exists
echo "Created_at column: ";
$createdCheck = $conn->query("SHOW COLUMNS FROM users LIKE 'created_at'");
if ($createdCheck && $createdCheck->num_rows > 0) {
    echo "✓ Exists\n";
} else {
    echo "✗ MISSING\n";
}

// Test the problematic query
echo "Test query: ";
$testQuery = "SELECT u.id, u.name, u.email, u.role, u.created_at, COUNT(CASE WHEN ib.status='issued' THEN 1 END) as issued_books FROM users u LEFT JOIN issued_books ib ON u.id=ib.user_id GROUP BY u.id LIMIT 1";
$result = $conn->query($testQuery);
if ($result) {
    echo "✓ Works\n";
} else {
    echo "✗ FAILED: " . $conn->error . "\n";
}

// Show all columns  
echo "\nAll columns in users table:\n";
$allCols = $conn->query("SHOW COLUMNS FROM users");
while ($col = $allCols->fetch_assoc()) {
    echo "  - " . $col['Field'] . " (" . $col['Type'] . ")\n";
}

$conn->close();
?>

<?php
// Comprehensive database setup and verification
$host = "localhost";
$user = "root";
$pass = "NewPassword123";
$dbname = "library_pro";

// Connect without specifying database first
$conn = new mysqli($host, $user, $pass);

if ($conn->connect_error) {
    die("<h2 style='color:red;'>Connection Failed: " . $conn->connect_error . "</h2>");
}

echo "<h1>Library Management System - Database Setup</h1>";

// Create database if it doesn't exist
echo "<h2>Step 1: Creating Database</h2>";
if ($conn->query("CREATE DATABASE IF NOT EXISTS $dbname")) {
    echo "<p style='color: green;'>✓ Database '$dbname' ready</p>";
} else {
    die("<p style='color: red;'>✗ Failed to create database: " . $conn->error . "</p>");
}

// Select the database
$conn->select_db($dbname);

// Create users table with all required columns
echo "<h2>Step 2: Creating/Updating Users Table</h2>";
$usersTable = "CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100),
    email VARCHAR(100) UNIQUE,
    password VARCHAR(255),
    role ENUM('admin','user') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($usersTable)) {
    echo "<p style='color: green;'>✓ Users table ready</p>";
} else {
    die("<p style='color: red;'>✗ Failed to create users table: " . $conn->error . "</p>");
}

// Now ensure the columns exist in the users table
echo "<h2>Step 2.5: Ensuring Users Table Columns</h2>";

// Add role column if it doesn't exist
$roleExists = $conn->query("SHOW COLUMNS FROM users LIKE 'role'");
if ($roleExists->num_rows === 0) {
    if ($conn->query("ALTER TABLE users ADD COLUMN role ENUM('admin','user') DEFAULT 'user'")) {
        echo "<p style='color: green;'>✓ Added 'role' column</p>";
    } else {
        echo "<p style='color: orange;'>⚠ 'role' column issue: " . $conn->error . "</p>";
    }
} else {
    echo "<p style='color: green;'>✓ 'role' column exists</p>";
}

// Add created_at column if it doesn't exist
$createdExists = $conn->query("SHOW COLUMNS FROM users LIKE 'created_at'");
if ($createdExists->num_rows === 0) {
    if ($conn->query("ALTER TABLE users ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP")) {
        echo "<p style='color: green;'>✓ Added 'created_at' column</p>";
    } else {
        echo "<p style='color: orange;'>⚠ 'created_at' column issue: " . $conn->error . "</p>";
    }
} else {
    echo "<p style='color: green;'>✓ 'created_at' column exists</p>";
}

// Create books table
echo "<h2>Step 3: Creating Books Table</h2>";
$booksTable = "CREATE TABLE IF NOT EXISTS books (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200),
    author VARCHAR(150),
    category VARCHAR(100),
    quantity INT,
    available INT,
    image VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($booksTable)) {
    echo "<p style='color: green;'>✓ Books table ready</p>";
} else {
    die("<p style='color: red;'>✗ Failed to create books table: " . $conn->error . "</p>");
}

// Create issued_books table
echo "<h2>Step 4: Creating Issued Books Table</h2>";
$issuedBooksTable = "CREATE TABLE IF NOT EXISTS issued_books (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    book_id INT,
    issue_date DATE,
    return_date DATE,
    actual_return DATE,
    fine DECIMAL(10,2) DEFAULT 0,
    status ENUM('issued','returned') DEFAULT 'issued'
)";

if ($conn->query($issuedBooksTable)) {
    echo "<p style='color: green;'>✓ Issued Books table ready</p>";
} else {
    die("<p style='color: red;'>✗ Failed to create issued_books table: " . $conn->error . "</p>");
}

// Create book_requests table
echo "<h2>Step 5: Creating Book Requests Table</h2>";
$bookRequestsTable = "CREATE TABLE IF NOT EXISTS book_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    book_id INT NOT NULL,
    request_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    approved_date DATETIME NULL,
    approval_notes TEXT,
    status ENUM('pending','approved','rejected') DEFAULT 'pending',
    approved_by INT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (book_id) REFERENCES books(id),
    FOREIGN KEY (approved_by) REFERENCES users(id)
)";

if ($conn->query($bookRequestsTable)) {
    echo "<p style='color: green;'>✓ Book Requests table ready</p>";
} else {
    // If it fails due to foreign keys, it might exist, so check
    if (strpos($conn->error, 'already exists') !== false) {
        echo "<p style='color: green;'>✓ Book Requests table already exists</p>";
    } else {
        die("<p style='color: red;'>✗ Failed to create book_requests table: " . $conn->error . "</p>");
    }
}

// Verify users table structure
echo "<h2>Step 6: Verifying Users Table Structure</h2>";
$columns = $conn->query("SHOW COLUMNS FROM users");
$hasRole = false;
$hasCreatedAt = false;

echo "<p>Columns found:</p><ul>";
while ($col = $columns->fetch_assoc()) {
    echo "<li>" . $col['Field'] . " (" . $col['Type'] . ")</li>";
    if ($col['Field'] === 'role') $hasRole = true;
    if ($col['Field'] === 'created_at') $hasCreatedAt = true;
}
echo "</ul>";

if ($hasRole && $hasCreatedAt) {
    echo "<p style='color: green;'><strong>✓ All required columns exist!</strong></p>";
} else {
    echo "<p style='color: red;'><strong>✗ Missing columns:</strong></p>";
    echo "<ul>";
    if (!$hasRole) echo "<li>role</li>";
    if (!$hasCreatedAt) echo "<li>created_at</li>";
    echo "</ul>";
}

// Test the problematic query
echo "<h2>Step 7: Testing Query from users.php (Line 73)</h2>";
$testQuery = "SELECT u.id, u.name, u.email, u.role, u.created_at,
       COUNT(CASE WHEN ib.status='issued' THEN 1 END) as issued_books
FROM users u
LEFT JOIN issued_books ib ON u.id=ib.user_id
GROUP BY u.id
ORDER BY u.created_at DESC";

$result = $conn->query($testQuery);
if ($result) {
    echo "<p style='color: green;'>✓ Query executed successfully!</p>";
    echo "<p>Rows returned: " . $result->num_rows . "</p>";
} else {
    echo "<p style='color: red;'>✗ Query failed: " . $conn->error . "</p>";
}

// Final summary
echo "<h2 style='margin-top: 30px;'>Setup Summary</h2>";
if ($hasRole && $hasCreatedAt && $result) {
    echo "<div style='background-color: #2dd4bf; color: white; padding: 20px; border-radius: 5px; font-size: 18px;'>";
    echo "<strong>✓ SUCCESS! Database is properly configured.</strong><br>";
    echo "<p style='margin-top: 15px;'>Choose where to go next:</p>";
    echo "<p>";
    echo "<a href='login.php' style='color: white; text-decoration: none; background-color: #4969ff; padding: 10px 15px; border-radius: 3px; margin-right: 10px;'>Login Page</a>";
    echo "<a href='users.php' style='color: white; text-decoration: none; background-color: #4969ff; padding: 10px 15px; border-radius: 3px; margin-right: 10px;'>Users Page</a>";
    echo "<a href='dashboard.php' style='color: white; text-decoration: none; background-color: #4969ff; padding: 10px 15px; border-radius: 3px;'>Dashboard</a>";
    echo "</p>";
    echo "</div>";
} else {
    echo "<div style='background-color: #ff5860; color: white; padding: 20px; border-radius: 5px; font-size: 18px;'>";
    echo "<strong>✗ ERROR! Please check the issues above.</strong>";
    echo "<p>If the problem persists, please contact your system administrator or try:</p>";
    echo "<ol>";
    echo "<li>Logging out and logging back in</li>";
    echo "<li>Refreshing this page (F5)</li>";
    echo "<li>Checking your database connection settings in config/db.php</li>";
    echo "</ol>";
    echo "</div>";
}

$conn->close();
?>

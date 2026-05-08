<?php
session_start();
include 'config/db.php';

echo "<h2 style='margin: 20px;'>📋 Library System Setup Verification</h2>";
echo "<div style='margin: 20px; max-width: 800px;'>";

$issues = [];
$checks = [];

// Check 1: Users table role column
echo "<h4>1. Users Table Structure</h4>";
$result = $conn->query("SHOW COLUMNS FROM users LIKE 'role'");
if ($result && $result->num_rows > 0) {
    echo "✅ 'role' column exists<br>";
    $checks[] = true;
} else {
    echo "❌ 'role' column missing - Adding...<br>";
    if ($conn->query("ALTER TABLE users ADD COLUMN role ENUM('admin','user') DEFAULT 'user'")) {
        echo "✅ 'role' column added<br>";
        $checks[] = true;
    } else {
        echo "⚠️ Error: " . $conn->error . "<br>";
        $issues[] = "Role column";
        $checks[] = false;
    }
}

// Check 2: Users table created_at column
$result = $conn->query("SHOW COLUMNS FROM users LIKE 'created_at'");
if ($result && $result->num_rows > 0) {
    echo "✅ 'created_at' column exists<br>";
    $checks[] = true;
} else {
    echo "❌ 'created_at' column missing - Adding...<br>";
    if ($conn->query("ALTER TABLE users ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP")) {
        echo "✅ 'created_at' column added<br>";
        $checks[] = true;
    } else {
        echo "⚠️ Error: " . $conn->error . "<br>";
        $issues[] = "Created_at column";
        $checks[] = false;
    }
}

// Check 3: book_requests table
echo "<h4>2. Book Requests Table</h4>";
$result = $conn->query("SHOW TABLES LIKE 'book_requests'");
if ($result && $result->num_rows > 0) {
    echo "✅ 'book_requests' table exists<br>";
    $checks[] = true;
} else {
    echo "❌ 'book_requests' table missing - Creating...<br>";
    
    $createTable = "CREATE TABLE book_requests (
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
    
    if ($conn->query($createTable)) {
        echo "✅ 'book_requests' table created<br>";
        $checks[] = true;
    } else {
        echo "⚠️ Error: " . $conn->error . "<br>";
        $issues[] = "Book_requests table";
        $checks[] = false;
    }
}

// Check 4: Books count
echo "<h4>3. Content Check</h4>";
$bookCount = $conn->query("SELECT COUNT(*) as count FROM books")->fetch_assoc()['count'];
echo "✅ Total Books: <strong>$bookCount</strong><br>";

$userCount = $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'];
echo "✅ Total Users: <strong>$userCount</strong><br>";

$adminCount = $conn->query("SELECT COUNT(*) as count FROM users WHERE role='admin'")->fetch_assoc()['count'];
echo "✅ Admin Users: <strong>$adminCount</strong><br>";

if ($adminCount === 0) {
    $issues[] = "No admin users configured";
    echo "<br><strong>⚠️ Important:</strong> Visit <a href='admin_setup.php' style='color: #6c84ff; text-decoration: none;'>admin_setup.php</a> to create an admin account<br>";
}

// Summary
echo "<hr>";
if (empty($issues)) {
    echo "<p style='color: green; font-size: 18px; font-weight: bold;'>✅ All systems operational!</p>";
    echo "<p><a href='dashboard.php' style='padding: 10px 20px; background-color: #6c84ff; color: white; text-decoration: none; border-radius: 5px; display: inline-block;'>Go to Dashboard →</a></p>";
} else {
    echo "<p style='color: red; font-size: 18px; font-weight: bold;'>⚠️ Issues Found:</p>";
    echo "<ul>";
    foreach ($issues as $issue) {
        echo "<li>$issue</li>";
    }
    echo "</ul>";
}

echo "</div>";
?>

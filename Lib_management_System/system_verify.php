<?php
session_start();
include 'config/db.php';

echo "<!DOCTYPE html><html><head><title>System Verification</title></head><body style='font-family: Arial; padding: 20px;'>";
echo "<h1>📚 Library System - Complete Verification & Fix</h1>";

// 1. Verify all tables
echo "<h2>Step 1: Verifying Database Tables</h2>";

// Create book_requests table if missing
$table_check = $conn->query("SHOW TABLES LIKE 'book_requests'");
if ($table_check->num_rows === 0) {
    echo "Creating book_requests table...<br>";
    $sql = "CREATE TABLE IF NOT EXISTS book_requests (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        book_id INT NOT NULL,
        request_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        approved_date DATETIME NULL,
        approval_notes TEXT,
        status ENUM('pending','approved','rejected') DEFAULT 'pending',
        approved_by INT NULL,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE,
        FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL
    )";
    if ($conn->query($sql)) {
        echo "✅ book_requests table created<br>";
    } else {
        echo "❌ Error: " . $conn->error . "<br>";
    }
} else {
    echo "✅ book_requests table exists<br>";
}

// Verify issued_books table structure
$check_issued = $conn->query("SHOW TABLES LIKE 'issued_books'");
if ($check_issued->num_rows === 0) {
    echo "Creating issued_books table...<br>";
    $sql = "CREATE TABLE IF NOT EXISTS issued_books (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        book_id INT NOT NULL,
        issue_date DATE NOT NULL,
        return_date DATE NOT NULL,
        actual_return DATE NULL,
        fine DECIMAL(10, 2) DEFAULT 0,
        status ENUM('issued','returned') DEFAULT 'issued',
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE
    )";
    if ($conn->query($sql)) {
        echo "✅ issued_books table created<br>";
    } else {
        echo "❌ Error: " . $conn->error . "<br>";
    }
} else {
    echo "✅ issued_books table exists<br>";
}

// Verify users table has role column
$users_check = $conn->query("SHOW COLUMNS FROM users LIKE 'role'");
if ($users_check->num_rows === 0) {
    echo "Adding role column to users table...<br>";
    if ($conn->query("ALTER TABLE users ADD COLUMN role ENUM('admin','user') DEFAULT 'user'")) {
        echo "✅ role column added<br>";
    } else {
        echo "⚠️ role column might already exist or error: " . $conn->error . "<br>";
    }
} else {
    echo "✅ users table has role column<br>";
}

// Verify books table has image column
$image_check = $conn->query("SHOW COLUMNS FROM books LIKE 'image'");
if ($image_check->num_rows === 0) {
    echo "Adding image column to books table...<br>";
    if ($conn->query("ALTER TABLE books ADD COLUMN image VARCHAR(255)")) {
        echo "✅ image column added<br>";
    } else {
        echo "⚠️ image column might already exist or error: " . $conn->error . "<br>";
    }
} else {
    echo "✅ books table has image column<br>";
}

echo "<h2>Step 2: Database Integrity Check</h2>";

// Count records
$users_count = $conn->query("SELECT COUNT(*) as c FROM users")->fetch_assoc()['c'];
$books_count = $conn->query("SELECT COUNT(*) as c FROM books")->fetch_assoc()['c'];
$requests_count = $conn->query("SELECT COUNT(*) as c FROM book_requests")->fetch_assoc()['c'];
$issued_count = $conn->query("SELECT COUNT(*) as c FROM issued_books")->fetch_assoc()['c'];

echo "Users: $users_count<br>";
echo "Books: $books_count<br>";
echo "Book Requests: $requests_count<br>";
echo "Issued Books: $issued_count<br>";

echo "<h2>Step 3: Sample Data Check</h2>";

// Check if there are any users
$users_result = $conn->query("SELECT id, name, email, role FROM users LIMIT 3");
if ($users_result->num_rows > 0) {
    echo "Sample Users:<br>";
    while ($user = $users_result->fetch_assoc()) {
        echo "  - ID: {$user['id']}, Name: {$user['name']}, Role: {$user['role']}<br>";
    }
} else {
    echo "⚠️ No users found<br>";
}

// Check if there are any books
$books_result = $conn->query("SELECT id, title, quantity, available FROM books LIMIT 3");
if ($books_result->num_rows > 0) {
    echo "Sample Books:<br>";
    while ($book = $books_result->fetch_assoc()) {
        echo "  - ID: {$book['id']}, Title: {$book['title']}, Available: {$book['available']}/{$book['quantity']}<br>";
    }
} else {
    echo "⚠️ No books found<br>";
}

// Check if there are any requests
$requests_result = $conn->query("SELECT br.id, u.name, b.title, br.status, br.request_date FROM book_requests br JOIN users u ON br.user_id = u.id JOIN books b ON br.book_id = b.id LIMIT 5");
if ($requests_result->num_rows > 0) {
    echo "Sample Requests:<br>";
    while ($req = $requests_result->fetch_assoc()) {
        echo "  - ID: {$req['id']}, User: {$req['name']}, Book: {$req['title']}, Status: {$req['status']}<br>";
    }
} else {
    echo "⚠️ No requests found<br>";
}

// Check if there are any issued books
$issued_result = $conn->query("SELECT ib.id, u.name, b.title, ib.status, ib.issue_date FROM issued_books ib JOIN users u ON ib.user_id = u.id JOIN books b ON ib.book_id = b.id LIMIT 5");
if ($issued_result->num_rows > 0) {
    echo "Sample Issued Books:<br>";
    while ($issued = $issued_result->fetch_assoc()) {
        echo "  - ID: {$issued['id']}, User: {$issued['name']}, Book: {$issued['title']}, Status: {$issued['status']}<br>";
    }
} else {
    echo "⚠️ No issued books found<br>";
}

echo "<h2>Step 4: System Status</h2>";
echo "<p style='color: green;'><strong>✅ Database verification complete!</strong></p>";
echo "<p>All required tables and columns have been verified/created.</p>";
echo "<p>The system is ready to use. Try the following workflow:</p>";
echo "<ol>";
echo "<li>User: Visit 'Request Book' and request a book</li>";
echo "<li>Admin: Visit 'Book Requests' and approve the request</li>";
echo "<li>User: Visit 'My Books & Penalties' to see the issued book</li>";
echo "</ol>";

echo "<a href='dashboard.php' style='padding: 10px 20px; background: #6c84ff; color: white; text-decoration: none; border-radius: 5px;'>← Back to Dashboard</a>";
echo "</body></html>";
?>

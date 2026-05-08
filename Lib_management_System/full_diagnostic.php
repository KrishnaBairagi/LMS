<?php
session_start();
include 'config/db.php';

if (!isset($_SESSION['user_id'])) {
    die("Not logged in. User ID: " . ($_SESSION['user_id'] ?? 'NONE'));
}

$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['role'] ?? 'unknown';

echo "<html><head><title>System Diagnostic</title></head><body>";
echo "<h1>Library Management System - Complete Diagnostic</h1>";
echo "<p>Current User: " . htmlspecialchars($_SESSION['user'] ?? 'Unknown') . " (ID: $user_id, Role: $user_role)</p>";
echo "<hr>";

// 1. Check session
echo "<h2>1. Session Information:</h2>";
echo "Session User: " . htmlspecialchars($_SESSION['user'] ?? 'NOT SET') . "<br>";
echo "Session User ID: " . htmlspecialchars($_SESSION['user_id'] ?? 'NOT SET') . "<br>";
echo "Session Role: " . htmlspecialchars($_SESSION['role'] ?? 'NOT SET') . "<br>";
echo "<hr>";

// 2. Check tables exist
echo "<h2>2. Database Tables:</h2>";
$tables = ['users', 'books', 'book_requests', 'issued_books'];
foreach ($tables as $table) {
    $check = $conn->query("SHOW TABLES LIKE '$table'");
    $exists = $check->num_rows > 0 ? '✅ EXISTS' : '❌ MISSING';
    echo "$table: $exists<br>";
}
echo "<hr>";

// 3. Check user record
echo "<h2>3. Current User Record:</h2>";
$userQuery = $conn->query("SELECT * FROM users WHERE id=$user_id");
if ($userQuery->num_rows > 0) {
    $userData = $userQuery->fetch_assoc();
    foreach ($userData as $key => $val) {
        echo htmlspecialchars("$key: $val") . "<br>";
    }
} else {
    echo "❌ User not found!<br>";
}
echo "<hr>";

// 4. Book requests count
echo "<h2>4. Book Requests (All):</h2>";
$all_reqs = $conn->query("SELECT COUNT(*) as count FROM book_requests");
$count = $all_reqs->fetch_assoc()['count'];
echo "Total book requests in system: $count<br>";

// 4.1 My pending requests
echo "<h3>My Pending Requests:</h3>";
$my_pending = $conn->query("SELECT br.*, b.title FROM book_requests br JOIN books b ON br.book_id = b.id WHERE br.user_id=$user_id AND br.status='pending'");
if ($my_pending->num_rows > 0) {
    while ($row = $my_pending->fetch_assoc()) {
        echo "ID: {$row['id']}, Book: {$row['title']}, Status: {$row['status']}, Date: {$row['request_date']}<br>";
    }
} else {
    echo "No pending requests<br>";
}

// 4.2 My approved requests
echo "<h3>My Approved Requests:</h3>";
$my_approved = $conn->query("SELECT br.*, b.title FROM book_requests br JOIN books b ON br.book_id = b.id WHERE br.user_id=$user_id AND br.status='approved'");
if ($my_approved->num_rows > 0) {
    while ($row = $my_approved->fetch_assoc()) {
        echo "ID: {$row['id']}, Book: {$row['title']}, Status: {$row['status']}, Approved Date: {$row['approved_date']}<br>";
    }
} else {
    echo "No approved requests<br>";
}
echo "<hr>";

// 5. Issued books
echo "<h2>5. Issued Books:</h2>";
$issued = $conn->query("SELECT ib.*, b.title FROM issued_books ib JOIN books b ON ib.book_id = b.id WHERE ib.user_id=$user_id");
if ($issued->num_rows > 0) {
    echo "Count: " . $issued->num_rows . "<br>";
    $issued->data_seek(0);
    while ($row = $issued->fetch_assoc()) {
        echo "- ID: {$row['id']}, Book: {$row['title']}, Status: {$row['status']}, Issue Date: {$row['issue_date']}, Return Date: {$row['return_date']}<br>";
    }
} else {
    echo "❌ No issued books found!<br>";
}
echo "<hr>";

// 6. Book availability check
echo "<h2>6. Sample Books (first 3):</h2>";
$books = $conn->query("SELECT id, title, quantity, available FROM books LIMIT 3");
if ($books->num_rows > 0) {
    while ($row = $books->fetch_assoc()) {
        echo "ID: {$row['id']}, Title: {$row['title']}, Quantity: {$row['quantity']}, Available: {$row['available']}<br>";
    }
} else {
    echo "❌ No books found!<br>";
}
echo "<hr>";

// 7. Check query for issued_book.php
echo "<h2>7. Testing issued_book.php Query:</h2>";
$test_query = $conn->query("SELECT ib.id, b.title, b.author, ib.issue_date, ib.return_date, ib.status, ib.book_id
                            FROM issued_books ib
                            JOIN books b ON ib.book_id=b.id
                            WHERE ib.user_id=$user_id
                            ORDER BY ib.issue_date DESC");
if ($test_query) {
    echo "Query executed successfully. Rows: " . $test_query->num_rows . "<br>";
    if ($test_query->num_rows > 0) {
        while ($row = $test_query->fetch_assoc()) {
            echo "Row: " . json_encode($row) . "<br>";
        }
    }
} else {
    echo "❌ Query failed: " . $conn->error . "<br>";
}
echo "<hr>";

// 8. Admin view - all pending
if ($user_role === 'admin') {
    echo "<h2>8. Admin View - All Pending Requests:</h2>";
    $all_pending = $conn->query("SELECT br.*, u.name, b.title FROM book_requests br JOIN users u ON br.user_id = u.id JOIN books b ON br.book_id = b.id WHERE br.status='pending'");
    echo "Total pending: " . $all_pending->num_rows . "<br>";
    if ($all_pending->num_rows > 0) {
        while ($row = $all_pending->fetch_assoc()) {
            echo "User: {$row['name']}, Book: {$row['title']}, ID: {$row['id']}<br>";
        }
    }
}

?>
</body></html>

<?php
session_start();
include 'config/db.php';

if (!isset($_SESSION['user'])) {
    die("Not logged in");
}

$user_id = $_SESSION['user_id'];

echo "<h1>Debug: Book Requests & Issues for User ID: $user_id</h1>";

// Check pending requests
echo "<h2>Pending Requests:</h2>";
$pending = $conn->query("SELECT br.id, br.user_id, br.book_id, br.request_date, b.title FROM book_requests br JOIN books b ON br.book_id = b.id WHERE br.user_id=$user_id AND br.status='pending'");
if ($pending->num_rows > 0) {
    while($row = $pending->fetch_assoc()) {
        echo "ID: {$row['id']}, Book: {$row['title']}, Date: {$row['request_date']}<br>";
    }
} else {
    echo "No pending requests<br>";
}

// Check approved requests
echo "<h2>Approved Requests:</h2>";
$approved = $conn->query("SELECT br.id, br.user_id, br.book_id, br.approved_date, b.title FROM book_requests br JOIN books b ON br.book_id = b.id WHERE br.user_id=$user_id AND br.status='approved'");
if ($approved->num_rows > 0) {
    while($row = $approved->fetch_assoc()) {
        echo "ID: {$row['id']}, Book: {$row['title']}, Approved: {$row['approved_date']}<br>";
    }
} else {
    echo "No approved requests<br>";
}

// Check issued books
echo "<h2>Issued Books (issued_books table):</h2>";
$issued = $conn->query("SELECT ib.id, ib.user_id, ib.book_id, ib.issue_date, ib.status, b.title FROM issued_books ib JOIN books b ON ib.book_id = b.id WHERE ib.user_id=$user_id");
if ($issued->num_rows > 0) {
    echo "Found {$issued->num_rows} records<br>";
    while($row = $issued->fetch_assoc()) {
        echo "ID: {$row['id']}, Book: {$row['title']}, Status: {$row['status']}, Issue Date: {$row['issue_date']}<br>";
    }
} else {
    echo "No issued books found<br>";
}

// Check all requests for this user (all statuses)
echo "<h2>All Requests (all statuses):</h2>";
$all = $conn->query("SELECT id, user_id, book_id, status, request_date FROM book_requests WHERE user_id=$user_id ORDER BY request_date DESC");
echo "Total: {$all->num_rows}<br>";
while($row = $all->fetch_assoc()) {
    echo "ID: {$row['id']}, Book ID: {$row['book_id']}, Status: {$row['status']}, Date: {$row['request_date']}<br>";
}

// Check if there are any issues with the issued_books query
echo "<h2>Test Query (from issued_book.php):</h2>";
$test = $conn->query("SELECT ib.id, b.title, b.author, ib.issue_date, ib.return_date, ib.status
                     FROM issued_books ib
                     JOIN books b ON ib.book_id=b.id
                     WHERE ib.user_id='$user_id'
                     ORDER BY ib.issue_date DESC");
if ($test) {
    echo "Query successful - {$test->num_rows} rows<br>";
} else {
    echo "Query failed: " . $conn->error . "<br>";
}

?>

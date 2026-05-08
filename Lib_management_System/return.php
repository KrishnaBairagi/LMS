<?php
session_start();
include 'config/db.php';

// Check if user is logged in
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit();
}

$id = intval($_GET['id']);

// Get the issued book details
$bookQuery = $conn->query("SELECT user_id, book_id, return_date FROM issued_books WHERE id=$id");
$bookData = $bookQuery->fetch_assoc();

if (!$bookData) {
    header('Location: issued_book.php');
    exit();
}

// Check if user is either admin or the book owner
$is_admin = $_SESSION['role'] === 'admin';
$is_owner = $bookData['user_id'] == $_SESSION['user_id'];

if (!$is_admin && !$is_owner) {
    header('Location: issued_book.php');
    exit();
}

// Calculate fine
$today = date("Y-m-d");
$returnDate = $bookData['return_date'];

$fine = 0;
if ($today > $returnDate) {
    $days = (strtotime($today) - strtotime($returnDate)) / 86400;
    $fine = ceil($days) * 10;
}

// Update the book status to returned
$conn->query("UPDATE issued_books SET status='returned', actual_return='$today', fine='$fine' WHERE id=$id");

// Increment the book availability back
$conn->query("UPDATE books SET available = available + 1 WHERE id=" . $bookData['book_id']);

// Update any associated book requests for this book by this user to 'returned'
$conn->query("UPDATE book_requests SET status='returned' WHERE user_id=" . $bookData['user_id'] . " AND book_id=" . $bookData['book_id'] . " AND status IN ('pending', 'approved')");

// Redirect based on user role
if ($is_admin) {
    header('Location: issued_books.php');
} else {
    header('Location: issued_book.php');
}
?>

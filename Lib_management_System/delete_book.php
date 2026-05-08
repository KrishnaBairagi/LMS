<?php
session_start();
include 'config/db.php';

// Role-based access control
if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'admin') {
    header('Location: dashboard.php');
    exit();
}

$id = intval($_GET['id']);

// First, delete all book requests for this book
$conn->query("DELETE FROM book_requests WHERE book_id=$id");

// Then, delete all issued books records for this book
$conn->query("DELETE FROM issued_books WHERE book_id=$id");

// Finally, delete the book itself
$conn->query("DELETE FROM books WHERE id=$id");

header("Location: books.php");
?>

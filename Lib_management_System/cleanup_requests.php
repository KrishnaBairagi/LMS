<?php
session_start();
include 'config/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = intval($_SESSION['user_id']);

echo "<!DOCTYPE html><html><head><title>Cleanup Old Requests</title>
<link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css' rel='stylesheet'>
<style>body { padding: 20px; background: #0f1419; color: #f0f0f0; }</style>
</head><body>";

echo "<h1>🧹 Clean Up Old Test Requests</h1>";

// Get all requests for this user
$requests = $conn->query("SELECT id, book_id, status, request_date FROM book_requests WHERE user_id=$user_id ORDER BY request_date DESC");

if (isset($_POST['cleanup_all'])) {
    // Delete all requests
    $result = $conn->query("DELETE FROM book_requests WHERE user_id=$user_id");
    if ($result) {
        echo "<div class='alert alert-success'>";
        echo "✅ <strong>Cleanup Complete!</strong><br>";
        echo "Deleted " . $conn->affected_rows . " request(s) from database.";
        echo "</div>";
        echo "<p><a href='request_book.php' class='btn btn-primary'>Go Back to Request Book</a></p>";
    } else {
        echo "<div class='alert alert-danger'>Error: " . $conn->error . "</div>";
    }
    echo "</body></html>";
    exit;
}

echo "<p>This tool will permanently delete all your book requests from the system.</p>";

if ($requests->num_rows > 0) {
    echo "<h2>Requests to Delete:</h2>";
    echo "<table class='table table-dark'>";
    echo "<tr><th>ID</th><th>Book ID</th><th>Status</th><th>Requested</th></tr>";
    while ($row = $requests->fetch_assoc()) {
        echo "<tr>";
        echo "<td>{$row['id']}</td>";
        echo "<td>{$row['book_id']}</td>";
        echo "<td><span class='badge bg-" . ($row['status'] == 'pending' ? 'warning' : 'success') . "'>{$row['status']}</span></td>";
        echo "<td>" . date('M d, Y H:i', strtotime($row['request_date'])) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<form method='POST'>";
    echo "<button type='submit' name='cleanup_all' class='btn btn-danger btn-lg'>⚠️ Delete All Requests</button>";
    echo "</form>";
} else {
    echo "<div class='alert alert-info'>";
    echo "✅ You have no requests to clean up.";
    echo "</div>";
}

echo "<a href='request_book.php' class='btn btn-secondary mt-3'>← Back to Requests</a>";
echo "</body></html>";
?>

<?php
session_start();
include 'config/db.php';

if (!isset($_SESSION['user_id'])) {
    die("Not logged in");
}

$user_id = intval($_SESSION['user_id']);

echo "<!DOCTYPE html><html><head><title>Request Status Debug</title>
<link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css' rel='stylesheet'>
<style>body { padding: 20px; } .debug { background: #f5f5f5; padding: 15px; border-radius: 5px; margin: 10px 0; }</style>
</head><body>";

echo "<h1>🔍 Request Status Debugger</h1>";
echo "<p>User ID: <strong>$user_id</strong></p>";
echo "<hr>";

// 1. Check pending requests count
echo "<h2>Pending Requests (COUNT):</h2>";
$count_result = $conn->query("SELECT COUNT(*) as count FROM book_requests WHERE user_id=$user_id AND status='pending'");
$pending_count = $count_result->fetch_assoc()['count'];
echo "<div class='debug'>COUNT(*) FROM book_requests WHERE user_id=$user_id AND status='pending' = <strong>$pending_count</strong></div>";

// 2. Check ALL requests for this user
echo "<h2>All Your Requests:</h2>";
$all_req = $conn->query("SELECT id, book_id, status, request_date FROM book_requests WHERE user_id=$user_id ORDER BY request_date DESC");
if ($all_req->num_rows > 0) {
    echo "<div class='debug'>";
    echo "<strong>Found " . $all_req->num_rows . " requests:</strong><br>";
    while ($row = $all_req->fetch_assoc()) {
        echo "ID: {$row['id']}, Book ID: {$row['book_id']}, Status: {$row['status']}, Date: {$row['request_date']}<br>";
    }
    echo "</div>";
} else {
    echo "<div class='debug'><strong>No requests found</strong></div>";
}

// 3. Sample the LEFT JOIN query
echo "<h2>Sample LEFT JOIN Query (First 3 Books):</h2>";
$sample = $conn->query("
    SELECT b.id, b.title, br.id as br_id, br.status,
    CASE WHEN br.id IS NOT NULL AND br.status='pending' THEN 'pending' 
         WHEN br.id IS NOT NULL AND br.status='approved' THEN 'approved'
         ELSE 'none' END as request_status
    FROM books b
    LEFT JOIN book_requests br ON b.id=br.book_id AND br.user_id=$user_id
    LIMIT 3
");

echo "<table class='table table-sm'>";
echo "<tr><th>Book ID</th><th>Title</th><th>Request ID</th><th>Status</th><th>Computed Status</th></tr>";
while ($row = $sample->fetch_assoc()) {
    $status = $row['status'] ?? 'NULL';
    echo "<tr>";
    echo "<td>{$row['id']}</td>";
    echo "<td>" . htmlspecialchars($row['title']) . "</td>";
    echo "<td>" . ($row['br_id'] ?? 'NULL') . "</td>";
    echo "<td>$status</td>";
    echo "<td><strong>{$row['request_status']}</strong></td>";
    echo "</tr>";
}
echo "</table>";

// 4. Check if there are books with old/stale requests
echo "<h2>Cleanup: Remove Old Test Requests</h2>";
$old_reqs = $conn->query("SELECT id, book_id, status FROM book_requests WHERE user_id=$user_id");
if ($old_reqs->num_rows > 0) {
    echo "<p>Found " . $old_reqs->num_rows . " requests. Do you want to delete them for testing?</p>";
    echo "<form method='POST'>";
    echo "<button type='submit' name='cleanup' class='btn btn-danger'>Delete All My Test Requests</button>";
    echo "</form>";
    
    if (isset($_POST['cleanup'])) {
        $conn->query("DELETE FROM book_requests WHERE user_id=$user_id");
        echo "<div class='alert alert-success'>✅ Deleted all requests. Refresh the page.</div>";
    }
}

echo "<hr>";
echo "<a href='request_book.php' class='btn btn-primary'>← Back to Request Book</a>";
echo "</body></html>";
?>

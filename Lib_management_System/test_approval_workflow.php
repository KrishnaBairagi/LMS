<?php
session_start();
include 'config/db.php';

echo "<!DOCTYPE html><html><head><title>Test Approval Workflow</title><link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css' rel='stylesheet'></head><body style='padding:20px;'>";
echo "<h1>📝 Test Approval Workflow</h1>";

// Some test data
$test_user_id = 2; // Assume we have user with ID 2
$test_book_id = 1; // Assume we have book with ID 1

echo "<h2>Step 1: Create a test request</h2>";
$insert_result = $conn->query("INSERT INTO book_requests (user_id, book_id, request_date) VALUES ($test_user_id, $test_book_id, NOW())");
if ($insert_result) {
    $request_id = $conn->insert_id;
    echo "<p style='color: green;'>✅ Created test request ID: $request_id</p>";
} else {
    echo "<p style='color: red;'>❌ Error: " . $conn->error . "</p>";
    exit;
}

echo "<h2>Step 2: Verify request exists (before approval)</h2>";
$check1 = $conn->query("SELECT * FROM book_requests WHERE id=$request_id");
if ($check1->num_rows > 0) {
    $req = $check1->fetch_assoc();
    echo "<p style='color: green;'>✅ Request found - Status: {$req['status']}</p>";
} else {
    echo "<p style='color: red;'>❌ Request not found</p>";
}

echo "<h2>Step 3: Simulate admin approval</h2>";
$admin_id = 1; // Admin user
$issue_date = date('Y-m-d');
$return_date = date('Y-m-d', strtotime('+7 days'));

// Get book info
$book = $conn->query("SELECT available FROM books WHERE id=$test_book_id")->fetch_assoc();
echo "<p>Book availability before: {$book['available']}</p>";

if ($book['available'] > 0) {
    // Insert into issued_books
    $insert_issued = $conn->query("INSERT INTO issued_books (user_id, book_id, issue_date, return_date, status) VALUES ($test_user_id, $test_book_id, '$issue_date', '$return_date', 'issued')");
    
    if ($insert_issued) {
        echo "<p style='color: green;'>✅ Created issued_books record</p>";
        
        // Update request
        $update_req = $conn->query("UPDATE book_requests SET status='approved', approved_date=NOW(), approved_by=$admin_id WHERE id=$request_id");
        if ($update_req) {
            echo "<p style='color: green;'>✅ Updated request status to approved</p>";
        } else {
            echo "<p style='color: red;'>❌ Failed to update request: " . $conn->error . "</p>";
        }
        
        // Update book availability
        $update_book = $conn->query("UPDATE books SET available=available-1 WHERE id=$test_book_id");
        if ($update_book) {
            echo "<p style='color: green;'>✅ Decreased book availability</p>";
        } else {
            echo "<p style='color: red;'>❌ Failed to update book: " . $conn->error . "</p>";
        }
    } else {
        echo "<p style='color: red;'>❌ Failed to create issued_books record: " . $conn->error . "</p>";
    }
} else {
    echo "<p style='color: red;'>❌ Book not available</p>";
}

echo "<h2>Step 4: Verify changes</h2>";

// Check request status
$check_req = $conn->query("SELECT status FROM book_requests WHERE id=$request_id")->fetch_assoc();
echo "<p>Request status: <strong>{$check_req['status']}</strong></p>";

// Check issued_books
$check_issued = $conn->query("SELECT * FROM issued_books WHERE user_id=$test_user_id AND book_id=$test_book_id");
if ($check_issued->num_rows > 0) {
    echo "<p style='color: green;'>✅ Book found in issued_books table</p>";
    $issued = $check_issued->fetch_assoc();
    echo "<ul>";
    echo "<li>User ID: {$issued['user_id']}</li>";
    echo "<li>Book ID: {$issued['book_id']}</li>";
    echo "<li>Status: {$issued['status']}</li>";
    echo "<li>Issue Date: {$issued['issue_date']}</li>";
    echo "</ul>";
} else {
    echo "<p style='color: red;'>❌ Book NOT found in issued_books table!</p>";
}

// Check book availability
$check_book = $conn->query("SELECT available FROM books WHERE id=$test_book_id")->fetch_assoc();
echo "<p>Book availability after: {$check_book['available']}</p>";

echo "<h2>Step 5: Query from user perspective (issued_book.php)</h2>";
$user_query = $conn->query("SELECT ib.id, b.title, ib.issue_date FROM issued_books ib JOIN books b ON ib.book_id = b.id WHERE ib.user_id = $test_user_id");
if ($user_query->num_rows > 0) {
    echo "<p style='color: green;'>✅ Query returned results</p>";
    while ($row = $user_query->fetch_assoc()) {
        echo "<p>Book: {$row['title']}, Issued: {$row['issue_date']}</p>";
    }
} else {
    echo "<p style='color: red;'>❌ Query returned NO results!</p>";
}

echo "<h2>Step 6: Cleanup (remove test data)</h2>";
$conn->query("DELETE FROM issued_books WHERE user_id=$test_user_id AND book_id=$test_book_id");
$conn->query("DELETE FROM book_requests WHERE id=$request_id");
$conn->query("UPDATE books SET available=available+1 WHERE id=$test_book_id");
echo "<p style='color: blue;'>ℹ️ Test data has been removed</p>";

echo "<a href='dashboard.php' class='btn btn-primary mt-3'>← Back to Dashboard</a>";
echo "</body></html>";
?>

<?php
session_start();
include 'config/db.php';

if (!isset($_SESSION['user_id'])) {
    die("Please log in first");
}

$user_id = intval($_SESSION['user_id']);
$user_role = $_SESSION['role'] ?? 'user';

echo "<!DOCTYPE html><html><head><title>Request System Troubleshoot</title>
<link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css' rel='stylesheet'>
<style>
body { padding: 20px; }
.step { margin: 20px 0; }
.success { color: #155724; background: #d4edda; padding: 10px; border-radius: 5px; }
.error { color: #721c24; background: #f8d7da; padding: 10px; border-radius: 5px; }
.warning { color: #856404; background: #fff3cd; padding: 10px; border-radius: 5px; }
.info { color: #004085; background: #d1ecf1; padding: 10px; border-radius: 5px; }
code { background: #f5f5f5; padding: 2px 5px; border-radius: 3px; }
</style>
</head><body>";

echo "<h1>📊 Book Request System Troubleshoot</h1>";
echo "<p>Current User: <strong>" . htmlspecialchars($_SESSION['user'] ?? 'Unknown') . "</strong> (Role: <strong>$user_role</strong>)</p>";
echo "<hr>";

// Step 1: Database Connection
echo "<div class='step'>";
echo "<h2>Step 1: Database Connection</h2>";
if ($conn->ping()) {
    echo "<div class='success'>✅ Database connected</div>";
} else {
    echo "<div class='error'>❌ Database not connected: " . $conn->error . "</div>";
    exit;
}
echo "</div>";

// Step 2: Check Tables
echo "<div class='step'>";
echo "<h2>Step 2: Check Required Tables</h2>";
$tables = ['users', 'books', 'book_requests', 'issued_books'];
foreach ($tables as $table) {
    $result = $conn->query("SHOW TABLES LIKE '$table'");
    if ($result && $result->num_rows > 0) {
        echo "<div class='success'>✅ Table <code>$table</code> exists</div>";
    } else {
        echo "<div class='error'>❌ Table <code>$table</code> NOT FOUND</div>";
    }
}
echo "</div>";

// Step 3: Check User
echo "<div class='step'>";
echo "<h2>Step 3: Check Current User</h2>";
$user = $conn->query("SELECT * FROM users WHERE id=$user_id")->fetch_assoc();
if ($user) {
    echo "<div class='success'>✅ User found</div>";
    echo "<table class='table table-sm'>";
    foreach ($user as $k => $v) {
        echo "<tr><td><strong>$k</strong></td><td>" . htmlspecialchars($v ?? 'NULL') . "</td></tr>";
    }
    echo "</table>";
} else {
    echo "<div class='error'>❌ User NOT found</div>";
}
echo "</div>";

// Step 4: Check Books
echo "<div class='step'>";
echo "<h2>Step 4: Available Books</h2>";
$books = $conn->query("SELECT id, title, available FROM books WHERE available > 0 LIMIT 5");
if ($books && $books->num_rows > 0) {
    echo "<div class='success'>✅ Found " . $books->num_rows . " available books</div>";
    echo "<table class='table table-sm'>";
    echo "<tr><th>ID</th><th>Title</th><th>Available</th></tr>";
    while ($row = $books->fetch_assoc()) {
        echo "<tr><td>{$row['id']}</td><td>" . htmlspecialchars($row['title']) . "</td><td>{$row['available']}</td></tr>";
    }
    echo "</table>";
} else {
    echo "<div class='error'>❌ No available books found</div>";
}
echo "</div>";

// Step 5: Check User's Existing Requests
echo "<div class='step'>";
echo "<h2>Step 5: Your Existing Requests</h2>";
$my_requests = $conn->query("
    SELECT br.id, br.status, b.title, br.request_date
    FROM book_requests br
    JOIN books b ON br.book_id = b.id
    WHERE br.user_id = $user_id
");

if ($my_requests && $my_requests->num_rows > 0) {
    echo "<div class='info'>Found " . $my_requests->num_rows . " requests:</div>";
    echo "<table class='table table-sm'>";
    echo "<tr><th>ID</th><th>Book</th><th>Status</th><th>Date</th></tr>";
    while ($row = $my_requests->fetch_assoc()) {
        $status_color = $row['status'] == 'pending' ? 'warning' : ($row['status'] == 'approved' ? 'success' : 'danger');
        echo "<tr>";
        echo "<td>{$row['id']}</td>";
        echo "<td>" . htmlspecialchars($row['title']) . "</td>";
        echo "<td><span class='badge bg-$status_color'>{$row['status']}</span></td>";
        echo "<td>" . date('M d, Y H:i', strtotime($row['request_date'])) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<div class='warning'>⚠️ No requests yet</div>";
}
echo "</div>";

// Step 6: Admin Visibility
if ($user_role === 'admin') {
    echo "<div class='step'>";
    echo "<h2>Step 6: Admin - All Pending Requests</h2>";
    $pending = $conn->query("
        SELECT br.id, u.name, b.title, br.request_date, b.available
        FROM book_requests br
        JOIN users u ON br.user_id = u.id
        JOIN books b ON br.book_id = b.id
        WHERE br.status = 'pending'
        ORDER BY br.request_date DESC
    ");
    
    if ($pending && $pending->num_rows > 0) {
        echo "<div class='success'>✅ Admin can see " . $pending->num_rows . " pending requests:</div>";
        echo "<table class='table table-sm'>";
        echo "<tr><th>ID</th><th>User</th><th>Book</th><th>Available</th><th>Date</th><th>Action</th></tr>";
        while ($row = $pending->fetch_assoc()) {
            $btn_disabled = $row['available'] <= 0 ? 'disabled' : '';
            echo "<tr>";
            echo "<td>{$row['id']}</td>";
            echo "<td>" . htmlspecialchars($row['name']) . "</td>";
            echo "<td>" . htmlspecialchars($row['title']) . "</td>";
            echo "<td><span class='badge bg-" . ($row['available'] > 0 ? 'success' : 'danger') . "'>" . $row['available'] . "</span></td>";
            echo "<td>" . date('M d, Y H:i', strtotime($row['request_date'])) . "</td>";
            echo "<td><a href='manage_requests.php' class='btn btn-sm btn-primary'>Go To</a></td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<div class='warning'>⚠️ No pending requests for admin to see</div>";
    }
    echo "</div>";
} else {
    echo "<div class='step'>";
    echo "<h2>Step 6: Your Request Status</h2>";
    $pending = $conn->query("SELECT COUNT(*) as count FROM book_requests WHERE user_id=$user_id AND status='pending'");
    $pending_count = $pending->fetch_assoc()['count'] ?? 0;
    
    if ($pending_count > 0) {
        echo "<div class='warning'>⚠️ You have <strong>$pending_count</strong> pending request(s)</div>";
        echo "<p>These should be visible to admin in the 'Book Requests' page</p>";
    } else {
        echo "<div class='info'>ℹ️ You have no pending requests</div>";
    }
    echo "</div>";
}

// Step 7: Test Request Submission
echo "<div class='step'>";
echo "<h2>Step 7: Test Request Submission</h2>";

// Find an available book for testing
$test_book = $conn->query("SELECT id, title FROM books WHERE available > 0 LIMIT 1");

if ($test_book && $test_book->num_rows > 0) {
    $book = $test_book->fetch_assoc();
    $book_id = $book['id'];
    
    // Check if this user already requested this book
    $existing = $conn->query("SELECT id, status FROM book_requests WHERE user_id=$user_id AND book_id=$book_id");
    
    if ($existing && $existing->num_rows > 0) {
        $req = $existing->fetch_assoc();
        echo "<div class='warning'>⚠️ You already have a request for this book (Status: {$req['status']})</div>";
        echo "<p><a href='request_book.php' class='btn btn-primary'>Go Back to Request Page</a></p>";
    } else {
        echo "<div class='info'>You can request: <strong>" . htmlspecialchars($book['title']) . "</strong></div>";
        echo "<form method='POST' style='margin-top: 10px;'>";
        echo "<input type='hidden' name='test_book_id' value='$book_id'>";
        echo "<button type='submit' name='submit_test' class='btn btn-success'>Test Submit Request for \"" . htmlspecialchars($book['title']) . "\"</button>";
        echo "</form>";
        
        // Handle test submission
        if (isset($_POST['submit_test'])) {
            $test_id = intval($_POST['test_book_id']);
            $insert = $conn->query("INSERT INTO book_requests (user_id, book_id, status, request_date) VALUES ($user_id, $test_id, 'pending', NOW())");
            
            if ($insert) {
                $req_id = $conn->insert_id;
                echo "<div class='success' style='margin-top: 10px;'>✅ Test request created! ID: $req_id</div>";
                echo "<p>This request should now be visible to admins in the 'Book Requests' page.</p>";
            } else {
                echo "<div class='error' style='margin-top: 10px;'>❌ Failed to create request: " . $conn->error . "</div>";
            }
        }
    }
} else {
    echo "<div class='error'>❌ No available books to test with</div>";
}

echo "</div>";

// Step 8: Summary & Recommendations
echo "<div class='step'>";
echo "<h2>Step 8: Summary & Next Steps</h2>";

$my_pending = $conn->query("SELECT COUNT(*) as count FROM book_requests WHERE user_id=$user_id AND status='pending'")->fetch_assoc()['count'];
$admin_can_see = $conn->query("SELECT COUNT(*) as count FROM book_requests WHERE status='pending'")->fetch_assoc()['count'];

echo "<table class='table'>";
echo "<tr><td><strong>Your Pending Requests:</strong></td><td>$my_pending</td></tr>";
echo "<tr><td><strong>Total Pending (Admin sees):</strong></td><td>$admin_can_see</td></tr>";
echo "<tr><td><strong>Status:</strong></td>";

if ($my_pending > 0 && $admin_can_see > 0) {
    echo "<td><div class='success'>✅ Requests are accessible to admin</div></td>";
} elseif ($my_pending > 0 && $admin_can_see == 0) {
    echo "<td><div class='error'>❌ Admin CANNOT see your requests!</div></td>";
} else {
    echo "<td><div class='info'>ℹ️ No requests to display</div></td>";
}

echo "</tr>";
echo "</table>";

echo "<h3>Recommended Actions:</h3>";
if ($user_role === 'user' && $my_pending == 0) {
    echo "<ol>";
    echo "<li>Go to <a href='request_book.php'>Request Books Page</a></li>";
    echo "<li>Click 'Request Book' on any available book</li>";
    echo "<li>Return here to verify the request was saved</li>";
    echo "</ol>";
} elseif ($user_role === 'admin') {
    echo "<ol>";
    echo "<li>Go to <a href='manage_requests.php'>Book Requests Management</a></li>";
    echo "<li>You should see pending requests there</li>";
    echo "<li>Click Approve (✅) to issue the book to the user</li>";
    echo "</ol>";
}

echo "</div>";

echo "<a href='dashboard.php' class='btn btn-secondary'>← Back to Dashboard</a>";
echo "</body></html>";
?>

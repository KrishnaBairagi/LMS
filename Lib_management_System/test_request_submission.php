<?php
session_start();
include 'config/db.php';

echo "<!DOCTYPE html><html><head><title>Test Book Request</title><link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css' rel='stylesheet'></head><body style='padding:20px;'>";
echo "<h1>🧪 Test Book Request Workflow</h1>";

if (!isset($_SESSION['user_id'])) {
    echo "<p style='color: red;'>❌ Not logged in. Please log in first.</p>";
    echo "<a href='login.php' class='btn btn-primary'>Go to Login</a>";
    echo "</body></html>";
    exit;
}

$user_id = intval($_SESSION['user_id']);
$user_name = htmlspecialchars($_SESSION['user'] ?? 'Unknown');

echo "<p><strong>Current User:</strong> $user_name (ID: $user_id)</p>";
echo "<hr>";

// 1. Check if user exists
echo "<h2>Step 1: Check User in Database</h2>";
$user_check = $conn->query("SELECT id, name, role FROM users WHERE id=$user_id");
if ($user_check->num_rows > 0) {
    $user = $user_check->fetch_assoc();
    echo "<p style='color: green;'>✅ User found: {$user['name']} (Role: {$user['role']})</p>";
} else {
    echo "<p style='color: red;'>❌ User not found in database!</p>";
}
echo "<hr>";

// 2. Check available books
echo "<h2>Step 2: Check Available Books</h2>";
$books = $conn->query("SELECT id, title, available FROM books WHERE available > 0 LIMIT 3");
if ($books->num_rows > 0) {
    echo "<p style='color: green;'>✅ Available books found:</p>";
    $test_book_id = null;
    while ($book = $books->fetch_assoc()) {
        echo "<li>ID: {$book['id']}, Title: {$book['title']}, Available: {$book['available']}</li>";
        if (!$test_book_id) $test_book_id = $book['id'];
    }
} else {
    echo "<p style='color: red;'>❌ No available books!</p>";
    echo "</body></html>";
    exit;
}
echo "<hr>";

// 3. Simulate request submission
if ($test_book_id) {
    echo "<h2>Step 3: Simulate Book Request</h2>";
    echo "<p>Testing with Book ID: $test_book_id and User ID: $user_id</p>";
    
    // Check if request already exists
    $existing = $conn->query("SELECT id, status FROM book_requests WHERE user_id=$user_id AND book_id=$test_book_id");
    
    if ($existing && $existing->num_rows > 0) {
        $req = $existing->fetch_assoc();
        echo "<p style='color: orange;'>⚠️ Request already exists (Status: {$req['status']})</p>";
        echo "<p>Deleting old request for fresh test...</p>";
        $conn->query("DELETE FROM book_requests WHERE id={$req['id']}");
        echo "<p style='color: green;'>✅ Deleted</p>";
    }
    
    // Insert new request
    $insert_sql = "INSERT INTO book_requests (user_id, book_id, status, request_date) VALUES ($user_id, $test_book_id, 'pending', NOW())";
    echo "<p><strong>Executing:</strong> <code>$insert_sql</code></p>";
    
    $result = $conn->query($insert_sql);
    if ($result) {
        $request_id = $conn->insert_id;
        echo "<p style='color: green;'>✅ Request inserted! Request ID: $request_id</p>";
    } else {
        echo "<p style='color: red;'>❌ Insert failed: " . $conn->error . "</p>";
        echo "</body></html>";
        exit;
    }
    echo "<hr>";
    
    // 4. Verify request in database
    echo "<h2>Step 4: Verify Request in Database</h2>";
    $verify = $conn->query("SELECT * FROM book_requests WHERE user_id=$user_id AND book_id=$test_book_id");
    if ($verify->num_rows > 0) {
        $req = $verify->fetch_assoc();
        echo "<p style='color: green;'>✅ Request found!</p>";
        echo "<ul>";
        foreach ($req as $key => $val) {
            echo "<li><strong>$key:</strong> " . htmlspecialchars($val ?? 'NULL') . "</li>";
        }
        echo "</ul>";
    } else {
        echo "<p style='color: red;'>❌ Request NOT found in database after insert!</p>";
    }
    echo "<hr>";
    
    // 5. Check how admin sees it
    echo "<h2>Step 5: Admin View - Pending Requests</h2>";
    $admin_view = $conn->query("
        SELECT br.id, u.name, b.title, br.status, br.request_date
        FROM book_requests br
        JOIN users u ON br.user_id = u.id
        JOIN books b ON br.book_id = b.id
        WHERE br.status = 'pending'
        ORDER BY br.request_date DESC
    ");
    
    if ($admin_view->num_rows > 0) {
        echo "<p style='color: green;'>✅ Admin can see these pending requests:</p>";
        echo "<table class='table'>";
        echo "<tr><th>ID</th><th>User</th><th>Book</th><th>Status</th><th>Date</th></tr>";
        $found = false;
        while ($req = $admin_view->fetch_assoc()) {
            $highlight = ($req['id'] == $request_id) ? " style='background-color: #d4edda;'" : "";
            echo "<tr$highlight>";
            echo "<td>{$req['id']}</td>";
            echo "<td>{$req['name']}</td>";
            echo "<td>{$req['title']}</td>";
            echo "<td><strong>{$req['status']}</strong></td>";
            echo "<td>" . date('M d, Y H:i', strtotime($req['request_date'])) . "</td>";
            echo "</tr>";
            if ($req['id'] == $request_id) $found = true;
        }
        echo "</table>";
        
        if ($found) {
            echo "<p style='color: green;'>✅✅✅ YOUR REQUEST IS VISIBLE TO ADMIN!</p>";
        } else {
            echo "<p style='color: orange;'>⚠️ Request created but not showing in admin view</p>";
        }
    } else {
        echo "<p style='color: red;'>❌ No pending requests visible to admin!</p>";
    }
    echo "<hr>";
}

echo "<h2>Cleanup</h2>";
echo "<p>Test request will be kept for admin to approve.</p>";
echo "<p>If successful, admin should see this request in 'Book Requests' page.</p>";
echo "<a href='dashboard.php' class='btn btn-primary'>← Back to Dashboard</a>";
echo "</body></html>";
?>

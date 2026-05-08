<?php
// Test database connection and queries
$host = "localhost";
$user = "root";
$pass = "NewPassword123";
$dbname = "library_pro";

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "Connected successfully\n";

// Test the query from line 73
$test_query = "SELECT u.id, u.name, u.email, u.role, u.created_at,
       COUNT(CASE WHEN ib.status='issued' THEN 1 END) as issued_books
FROM users u
LEFT JOIN issued_books ib ON u.id=ib.user_id
GROUP BY u.id
ORDER BY u.created_at DESC";

echo "Executing query: " . substr($test_query, 0, 50) . "...\n";

$result = $conn->query($test_query);
if ($result) {
    echo "SUCCESS! Query executed\n";
    echo "Rows: " . $result->num_rows . "\n";
} else {
    echo "FAILED: " . $conn->error . "\n";
}

$conn->close();
?>

<?php
// Verify database setup
include 'config/db.php';

echo "<h1>Database Verification</h1>";

// Check if columns exist
$query = $conn->query("SHOW COLUMNS FROM users");
$hasRole = false;
$hasCreatedAt = false;

echo "<h2>Users Table Columns:</h2><ul>";
while ($col = $query->fetch_assoc()) {
    echo "<li><strong>" . $col['Field'] . "</strong> (" . $col['Type'] . ")</li>";
    if ($col['Field'] === 'role') $hasRole = true;
    if ($col['Field'] === 'created_at') $hasCreatedAt = true;
}
echo "</ul>";

if ($hasRole && $hasCreatedAt) {
    echo "<div style='background-color: #2dd4bf; color: white; padding: 15px; border-radius: 5px;'>";
    echo "<h2>✅ SUCCESS!</h2>";
    echo "<p>All required columns exist in the users table.</p>";
    echo "<p><a href='users.php' style='color: white; text-decoration: underline;'><strong>Click here to access Users page</strong></a></p>";
    echo "</div>";
} else {
    echo "<div style='background-color: #ff5860; color: white; padding: 15px; border-radius: 5px;'>";
    echo "<h2>❌ ERROR</h2>";
    echo "<p>Missing columns:</p><ul>";
    if (!$hasRole) echo "<li>role</li>";
    if (!$hasCreatedAt) echo "<li>created_at</li>";
    echo "</ul></div>";
}
?>

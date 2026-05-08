<?php
include 'config/db.php';

echo "<h2>Database Diagnostic & Repair</h2>";

// Check users table structure
echo "<h3>Checking users table columns:</h3>";
$columns = $conn->query("SHOW COLUMNS FROM users");
$columnNames = [];
echo "<ul>";
while ($col = $columns->fetch_assoc()) {
    $columnNames[] = $col['Field'];
    echo "<li>" . $col['Field'] . " (" . $col['Type'] . ")</li>";
}
echo "</ul>";

// Check if role column exists
if (in_array('role', $columnNames)) {
    echo "<p style='color: green;'>✅ <strong>role</strong> column exists</p>";
} else {
    echo "<p style='color: red;'>❌ <strong>role</strong> column MISSING - attempting to add...</p>";
    $result = $conn->query("ALTER TABLE users ADD COLUMN role ENUM('admin','user') DEFAULT 'user'");
    if ($result) {
        echo "<p style='color: green;'>✅ Successfully added <strong>role</strong> column</p>";
    } else {
        echo "<p style='color: red;'>❌ Error adding role column: " . $conn->error . "</p>";
    }
}

// Check if created_at column exists
if (in_array('created_at', $columnNames)) {
    echo "<p style='color: green;'>✅ <strong>created_at</strong> column exists</p>";
} else {
    echo "<p style='color: red;'>❌ <strong>created_at</strong> column MISSING - attempting to add...</p>";
    $result = $conn->query("ALTER TABLE users ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP");
    if ($result) {
        echo "<p style='color: green;'>✅ Successfully added <strong>created_at</strong> column</p>";
    } else {
        echo "<p style='color: red;'>❌ Error adding created_at column: " . $conn->error . "</p>";
    }
}

// Final verification
echo "<h3>Final Verification:</h3>";
$finalCheck = $conn->query("SHOW COLUMNS FROM users");
$finalColumns = [];
while ($col = $finalCheck->fetch_assoc()) {
    $finalColumns[] = $col['Field'];
}

if (in_array('role', $finalColumns) && in_array('created_at', $finalColumns)) {
    echo "<p style='color: green; font-size: 18px;'><strong>✅ SUCCESS! All required columns exist.</strong></p>";
    echo "<p><a href='users.php'>Click here to go to Users page</a></p>";
} else {
    echo "<p style='color: red; font-size: 18px;'><strong>❌ ERROR: Some columns still missing!</strong></p>";
    echo "<p>Missing columns: ";
    if (!in_array('role', $finalColumns)) echo "role ";
    if (!in_array('created_at', $finalColumns)) echo "created_at ";
    echo "</p>";
}

// Show table structure
echo "<h3>Current table structure:</h3>";
echo "<pre>";
$schema = $conn->query("SHOW CREATE TABLE users");
if ($schema) {
    echo $schema->fetch_assoc()['Create Table'];
}
echo "</pre>";
?>

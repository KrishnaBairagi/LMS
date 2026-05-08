<?php
// Database connection
$conn = new mysqli("localhost", "root", "", "library_pro");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "<h2>Database Migration</h2>";
echo "<hr>";

// Step 1: Add role column if missing
echo "<p><strong>Step 1:</strong> Checking 'role' column...</p>";
$checkRole = $conn->query("SHOW COLUMNS FROM users LIKE 'role'");

if ($checkRole && $checkRole->num_rows === 0) {
    if ($conn->query("ALTER TABLE users ADD COLUMN role ENUM('admin','user') DEFAULT 'user'")) {
        echo "<p style='color:green'>✅ 'role' column added!</p>";
    } else {
        echo "<p style='color:red'>⚠️ Error adding role: " . $conn->error . "</p>";
    }
} else {
    echo "<p style='color:green'>✅ 'role' column exists!</p>";
}

// Step 2: Add created_at column if missing
echo "<p><strong>Step 2:</strong> Checking 'created_at' column...</p>";
$checkCreated = $conn->query("SHOW COLUMNS FROM users LIKE 'created_at'");

if ($checkCreated && $checkCreated->num_rows === 0) {
    if ($conn->query("ALTER TABLE users ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP")) {
        echo "<p style='color:green'>✅ 'created_at' column added!</p>";
    } else {
        echo "<p style='color:red'>⚠️ Error adding created_at: " . $conn->error . "</p>";
    }
} else {
    echo "<p style='color:green'>✅ 'created_at' column exists!</p>";
}

// Step 3: Verify all required columns exist
echo "<p><strong>Step 3:</strong> Verifying table structure...</p>";
echo "<table border='1' cellpadding='10' style='border-collapse: collapse; margin: 10px 0;'>";
echo "<tr style='background-color: #333; color: white;'><th>Column</th><th>Type</th><th>Null</th><th>Default</th></tr>";

$result = $conn->query("SHOW COLUMNS FROM users");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['Field'] . "</td>";
        echo "<td>" . $row['Type'] . "</td>";
        echo "<td>" . $row['Null'] . "</td>";
        echo "<td>" . ($row['Default'] ?? 'None') . "</td>";
        echo "</tr>";
    }
}
echo "</table>";

echo "<hr>";
echo "<p style='color:green;'><strong>✅ Database setup complete!</strong></p>";
echo "<p><a href='dashboard.php' style='padding: 10px 20px; background-color: #6c84ff; color: white; text-decoration: none; border-radius: 5px; display: inline-block;'>Go to Dashboard →</a></p>";

$conn->close();
?>


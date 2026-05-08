<?php
// Use the actual database config from the project
include 'config/db.php';

echo "=== Database Table Structure ===\n\n";

// Get all columns
$columns = $conn->query("DESC users");
if ($columns) {
    echo "Users table columns:\n";
    while ($col = $columns->fetch_assoc()) {
        echo "  - " . $col['Field'] . " (" . $col['Type'] . ")\n";
    }
} else {
    echo "Error querying columns: " . $conn->error . "\n";
}

// Try to add the columns
echo "\n=== Adding Missing Columns ===\n";

$result = $conn->query("ALTER TABLE users ADD COLUMN role ENUM('admin','user') DEFAULT 'user'");
if ($result) {
    echo "✓ Added role column\n";
} else {
    echo "✗ Role column: " . $conn->error . "\n";
}

$result = $conn->query("ALTER TABLE users ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP");
if ($result) {
    echo "✓ Added created_at column\n";
} else {
    echo "✗ Created_at column: " . $conn->error . "\n";
}

// Verify final structure
echo "\n=== Verification ===\n";
$columns = $conn->query("DESC users");
if ($columns) {
    echo "Final users table columns:\n";
    while ($col = $columns->fetch_assoc()) {
        echo "  - " . $col['Field'] . " (" . $col['Type'] . ")\n";
    }
}

$conn->close();
?>

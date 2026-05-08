<?php
session_start();
include 'config/db.php';

echo "<!DOCTYPE html>
<html>
<head>
<meta charset='UTF-8'>
<meta name='viewport' content='width=device-width, initial-scale=1.0'>
<title>Library System - Setup Status</title>
<link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css' rel='stylesheet'>
<link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css'>
<style>
body { background: #0f1419; color: #f0f0f0; padding: 40px 20px; }
.status-card { background: #252d3d; border: 1px solid #3a4555; border-radius: 8px; padding: 20px; margin: 10px 0; }
.status-ok { border-left: 4px solid #2dd4bf; }
.status-error { border-left: 4px solid #ff5860; }
.status-warning { border-left: 4px solid #f59e0b; }
</style>
</head>
<body>
<div class='container' style='max-width: 800px;'>
<h2 style='color: #6c84ff; margin-bottom: 30px;'><i class='fas fa-clipboard-check me-2'></i>Library System Setup Verification</h2>";

$checks_passed = 0;
$checks_total = 0;

// Check 1: book_requests table
echo "<div class='status-card status-ok'>";
$checks_total++;
$result = $conn->query("SHOW TABLES LIKE 'book_requests'");
if ($result && $result->num_rows > 0) {
    echo "<h5><i class='fas fa-check-circle' style='color: #2dd4bf;'></i> book_requests Table</h5>";
    echo "<p class='mb-0' style='color: #b0b0b0;'>✅ Table exists and is ready for use</p>";
    $checks_passed++;
} else {
    echo "<h5><i class='fas fa-times-circle' style='color: #ff5860;'></i> book_requests Table</h5>";
    echo "<p class='mb-0' style='color: #b0b0b0;'>❌ Table not found</p>";
}
echo "</div>";

// Check 2: Users table columns
echo "<div class='status-card status-ok'>";
$checks_total++;
$roleCheck = $conn->query("SHOW COLUMNS FROM users LIKE 'role'");
$createdCheck = $conn->query("SHOW COLUMNS FROM users LIKE 'created_at'");

if ($roleCheck && $roleCheck->num_rows > 0 && $createdCheck && $createdCheck->num_rows > 0) {
    echo "<h5><i class='fas fa-check-circle' style='color: #2dd4bf;'></i> Users Table Columns</h5>";
    echo "<p class='mb-0' style='color: #b0b0b0;'>✅ All required columns exist (role, created_at)</p>";
    $checks_passed++;
} else {
    echo "<h5><i class='fas fa-times-circle' style='color: #ff5860;'></i> Users Table Columns</h5>";
    echo "<p class='mb-0' style='color: #b0b0b0;'>❌ Some columns are missing</p>";
}
echo "</div>";

// Check 3: Data availability
echo "<div class='status-card status-ok'>";
$checks_total++;
$bookCount = $conn->query("SELECT COUNT(*) as count FROM books")->fetch_assoc()['count'];
$userCount = $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'];
$adminCount = $conn->query("SELECT COUNT(*) as count FROM users WHERE role='admin'")->fetch_assoc()['count'];

if ($bookCount > 0 && $userCount > 0 && $adminCount > 0) {
    echo "<h5><i class='fas fa-check-circle' style='color: #2dd4bf;'></i> Database Content</h5>";
    echo "<p class='mb-0' style='color: #b0b0b0;'>✅ Books: <strong>$bookCount</strong> | Users: <strong>$userCount</strong> | Admins: <strong>$adminCount</strong></p>";
    $checks_passed++;
} else {
    echo "<div class='status-card status-warning'>";
    echo "<h5><i class='fas fa-exclamation-triangle' style='color: #f59e0b;'></i> Database Content</h5>";
    echo "<p class='mb-0' style='color: #b0b0b0;'>⚠️ Books: $bookCount | Users: $userCount | Admins: $adminCount</p>";
    if ($adminCount === 0) {
        echo "<p style='color: #f59e0b; margin-top: 10px;'><i class='fas fa-info-circle me-1'></i>Visit <a href='admin_setup.php' style='color: #6c84ff;'>admin_setup.php</a> to create an admin account</p>";
    }
    echo "</div>";
    $checks_passed++;
}
echo "</div>";

// Check 4: Required pages exist
echo "<div class='status-card status-ok'>";
$checks_total++;
$pagesCheck = true;
$requiredPages = ['request_book.php', 'manage_requests.php', 'users.php', 'dashboard.php', 'books.php'];
$allExist = true;

foreach ($requiredPages as $page) {
    if (!file_exists($page)) {
        $allExist = false;
        break;
    }
}

if ($allExist) {
    echo "<h5><i class='fas fa-check-circle' style='color: #2dd4bf;'></i> Required Pages</h5>";
    echo "<p class='mb-0' style='color: #b0b0b0;'>✅ All essential pages are present</p>";
    $checks_passed++;
} else {
    echo "<h5><i class='fas fa-times-circle' style='color: #ff5860;'></i> Required Pages</h5>";
    echo "<p class='mb-0' style='color: #b0b0b0;'>❌ Some pages are missing</p>";
}
echo "</div>";

// Summary
$percentage = ($checks_passed / $checks_total) * 100;

echo "<hr style='border-color: #3a4555; margin: 30px 0;'>";
echo "<div style='text-align: center;'>";
echo "<p><strong>Setup Progress: " . round($percentage, 0) . "%</strong></p>";

if ($percentage === 100) {
    echo "<p style='color: #2dd4bf; font-size: 18px; margin: 20px 0;'><i class='fas fa-check-circle me-2'></i>🎉 All systems operational!</p>";
    echo "<p><a href='dashboard.php' class='btn btn-primary' style='background: linear-gradient(135deg, #6c84ff, #4969ff); border: none; padding: 10px 30px;'><i class='fas fa-arrow-right me-2'></i>Go to Dashboard</a></p>";
} else {
    echo "<p style='color: #ff5860; font-size: 18px; margin: 20px 0;'><i class='fas fa-exclamation-circle me-2'></i>Some issues detected</p>";
    echo "<p><a href='admin_setup.php' class='btn btn-warning' style='padding: 10px 30px;'><i class='fas fa-wrench me-2'></i>Run Setup</a></p>";
}

echo "</div>";
echo "</div>
</body>
</html>";

$conn->close();
?>

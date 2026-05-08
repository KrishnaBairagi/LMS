<?php
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "library_pro";

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>

<?php
// Enable displaying of all errors and warnings
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
$servername = "localhost";
$username = "admin";
$password = "Sql_admin@#2024";
date_default_timezone_set('Asia/bangkok');
try {
$conn = new PDO("mysql:host=$servername;dbname=job", $username, $password);
// Set the PDO error mode to exception
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
// echo "Connected successfully";
} catch(PDOException $e) {
echo "Connection failed: " . $e->getMessage();
}
?>
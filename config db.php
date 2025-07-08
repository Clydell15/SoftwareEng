<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$host     = getenv('DB_HOST');
$port     = getenv('DB_PORT') ?: 3306;
$user     = getenv('DB_USER');
$password = getenv('DB_PASSWORD');
$database = getenv('DB_NAME');

$conn = new mysqli($host, $user, $password, $database, $port);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>

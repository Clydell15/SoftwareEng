<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }

$host     = getenv("DB_HOST");
$port     = getenv("DB_PORT") ?: "5432"; 
$dbname   = getenv("DB_NAME");
$user     = getenv("DB_USER");
$password = getenv("DB_PASSWORD");

$conn_str = "host=$host port=$port dbname=$dbname user=$user password=$password";
$conn = pg_connect($conn_str);

if (!$conn) {
    die("Connection failed: " . pg_last_error());
}
?>

<?php
if (!isset($_SESSION['user_id'])) {
    header("Location: authentication/auth.php");
    exit();
}

// Include your DB connection
include '../config db.php'; // Make sure this path is correct

$userId = $_SESSION['user_id'];

// Update the query to reflect the correct column names
$sql = "SELECT email, shortbreak_time, longbreak_time, pomodoro_time, pomodoro_goal FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();
    $_SESSION['user'] = $user; // Now $_SESSION['user'] is an associative array
} else {
    // User not found, logout
    session_destroy();
    header("Location: authentication/auth.php");
    exit();
}


?>

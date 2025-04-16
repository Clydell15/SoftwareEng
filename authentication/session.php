<?php
if (!isset($_SESSION['user_id'])) {
    header("Location: ../authentication/auth.php");
    exit();
}

$userId = $_SESSION['user_id'];


$sql = "SELECT email, shortbreak_time, longbreak_time, pomodoro_time, pomodoro_goal FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();
    $_SESSION['user'] = $user; 
} else {
    session_destroy();
    header("Location: authentication/auth.php");
    exit();
}


?>

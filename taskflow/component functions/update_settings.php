<?php
include '../../config db.php'; 
include '../../authentication/session.php';  


$userId = $_SESSION['user_id'];


if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $pomodoroGoal = $_POST['pomodoro_goal'];
    $pomodoroTime = $_POST['pomodoro_time'];
    $shortBreak = $_POST['shortbreak_time'];
    $longBreak = $_POST['longbreak_time'];


    $stmt = $conn->prepare("
        UPDATE users 
        SET pomodoro_goal = ?, pomodoro_time = ?, shortbreak_time = ?, longbreak_time = ? 
        WHERE id = ?
    ");
    $stmt->bind_param("iiiii", $pomodoroGoal, $pomodoroTime, $shortBreak, $longBreak, $userId);


    if ($stmt->execute()) {
        $_SESSION['pomodoro_success'] = "Pomodoro settings updated successfully.";
        header("Location: ../settings.php");
        exit();
    } else {
        $_SESSION['pomodoro_error'] = "Something went wrong. Please try again later.";
        header("Location: ../settings.php");
        exit();
    }
}
?>

<?php
include '../config db.php';
include '../authentication/session.php';

$userId = $_SESSION['user_id'];

// Handle POST request for updating session counts or resetting
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['reset'])) {
        // Reset all session counts to 0
        $stmt = $conn->prepare("UPDATE users SET pomodoro_sessions = 0, short_breaks = 0, long_breaks = 0 WHERE id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $stmt->close();
        echo "Sessions reset successfully!";
        exit;
    }

    if (isset($_POST['session_type'])) {
        $sessionType = $_POST['session_type'];
        $columnMap = [
            'pomodoro' => 'pomodoro_sessions',
            'shortBreak' => 'short_breaks',
            'longBreak' => 'long_breaks'
        ];

        // Check if the session type is valid and update the corresponding column
        if (array_key_exists($sessionType, $columnMap)) {
            $column = $columnMap[$sessionType];
            $stmt = $conn->prepare("UPDATE users SET $column = $column + 1 WHERE id = ?");
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $stmt->close();
            echo "Session count for $sessionType updated!";
            exit;
        } else {
            echo "Invalid session type!";
            exit;
        }
    }
}

// Fetch the current user data
$userQuery = $conn->prepare("SELECT pomodoro_sessions, short_breaks, long_breaks, pomodoro_goal FROM users WHERE id = ?");
$userQuery->bind_param("i", $userId);
$userQuery->execute();
$userResult = $userQuery->get_result();
$userdash = $userResult->fetch_assoc();
$userQuery->close();

// Ensure the goal value is valid (fallback to 0 if not set)
$goal = $userdash['pomodoro_goal'] ?? 0;
$sessions = $userdash['pomodoro_sessions'];
$progressPercent = ($goal > 0) ? min(100, ($sessions / $goal) * 100) : 0;

// Generate the motivational message based on progress
if ($progressPercent >= 100) {
    $message = "🎉 Goal achieved! Amazing work!";
} elseif ($progressPercent >= 75) {
    $message = "🔥 Almost there, keep it up!";
} elseif ($progressPercent >= 50) {
    $message = "🚀 You're halfway through!";
} else {
    $message = "💪 Let’s keep the momentum!";
}

// Fetch completed tasks count
$completedResult = $conn->query("SELECT COUNT(*) AS total FROM tasks WHERE user_id = $userId AND status = 'completed'");
if ($completedResult) {
    $row = $completedResult->fetch_assoc();
    $completedCount = $row['total'];
}

// Fetch pending tasks count
$pendingResult = $conn->query("SELECT COUNT(*) AS total FROM tasks WHERE user_id = $userId AND status = 'pending'");
if ($pendingResult) {
    $row = $pendingResult->fetch_assoc();
    $pendingCount = $row['total'];
}

// Fetch upcoming tasks
$now = date('Y-m-d H:i:s');
$upcomingQuery = $conn->prepare("
    SELECT id, title, due_date 
    FROM tasks 
    WHERE user_id = ? AND due_date IS NOT NULL AND due_date >= ? 
    ORDER BY due_date ASC 
    LIMIT 5
");
$upcomingQuery->bind_param("is", $userId, $now);
$upcomingQuery->execute();
$upcomingResult = $upcomingQuery->get_result();
$upcomingTasks = [];

while ($task = $upcomingResult->fetch_assoc()) {
    $upcomingTasks[] = $task;
}
$upcomingQuery->close();
?>

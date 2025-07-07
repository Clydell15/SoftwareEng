<?php
include '../../config db.php';
include '../../authentication/session.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["success" => false, "message" => "Invalid request"]);
    exit();
}

$userId = $_SESSION['user_id'];

if (!isset($_POST['taskId']) || empty($_POST['taskId'])) {
    echo json_encode(["success" => false, "message" => "Task ID is required"]);
    exit();
}

$taskId = intval($_POST['taskId']);

handleTaskArchiving($conn, $userId, $taskId);

// ========================
// 📌 FUNCTION: Archive Task/Subtask
// ========================
function handleTaskArchiving($conn, $userId, $taskId) {
    $parentTaskId = null;

    // Check if the task exists and retrieve its parent_task_id
    $stmt = $conn->prepare("SELECT parent_task_id FROM tasks WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $taskId, $userId);
    $stmt->execute();
    $stmt->bind_result($parentTaskId);

    if (!$stmt->fetch()) {
        echo json_encode(["success" => false, "message" => "Task not found or you don't have permission to archive it"]);
        $stmt->close();
        exit();
    }
    $stmt->close();

    // Archive the task (and optionally subtasks if parent)
    if (is_null($parentTaskId)) {
        // Archive the parent task and its subtasks
        $stmt = $conn->prepare("UPDATE tasks SET archived = 1 WHERE id = ? OR parent_task_id = ?");
        $stmt->bind_param("ii", $taskId, $taskId);
    } else {
        // Archive only the subtask
        $stmt = $conn->prepare("UPDATE tasks SET archived = 1 WHERE id = ?");
        $stmt->bind_param("i", $taskId);
    }

    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "Task deleted successfully"]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to delete task"]);
    }

    $stmt->close();
    exit();
}
?>
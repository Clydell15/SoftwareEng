<?php
require '../../config db.php';
date_default_timezone_set('Asia/Manila');

header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);
$task_id = $data['task_id'] ?? null;
$status = $data['status'] ?? null;

if (!$task_id || !$status) {
    echo json_encode(["success" => false, "message" => "Missing parameters"]);
    exit;
}

// Set completed_at to current date/time if completed, otherwise NULL
$completed_at = ($status === "completed") ? date("Y-m-d H:i:s") : null;

$conn->begin_transaction();

try {
    // Step 1: Get the user_id first
    $selectUserQuery = "SELECT user_id FROM tasks WHERE id = ?";
    $stmtSelectUser = $conn->prepare($selectUserQuery);
    $stmtSelectUser->bind_param("i", $task_id);
    $stmtSelectUser->execute();
    $stmtSelectUser->bind_result($user_id);
    $stmtSelectUser->fetch();
    $stmtSelectUser->close();

    // Then update positions
    $shiftQuery = "UPDATE tasks SET position = position + 1 WHERE user_id = ?";
    $stmtShift = $conn->prepare($shiftQuery);
    $stmtShift->bind_param("i", $user_id);
    $stmtShift->execute();
    $stmtShift->close();


    // Step 2: Set the updated task's position to 1
    $updateQuery = "UPDATE tasks SET status = ?, completed_at = ?, position = 1 WHERE id = ?";
    $stmtUpdate = $conn->prepare($updateQuery);
    $stmtUpdate->bind_param("ssi", $status, $completed_at, $task_id);
    $stmtUpdate->execute();
    $stmtUpdate->close();

    // Step 3: If parent task is marked completed, mark all its subtasks as completed
    if ($status === "completed") {
        $subtaskQuery = "UPDATE tasks SET status = 'completed', completed_at = ? WHERE parent_task_id = ?";
        $stmtSubtasks = $conn->prepare($subtaskQuery);
        $stmtSubtasks->bind_param("si", $completed_at, $task_id);
        $stmtSubtasks->execute();
        $stmtSubtasks->close();
    }

    // Step 4: If parent task is marked uncompleted, mark all its subtasks as uncompleted
    if ($status === "pending") {
        $subtaskQuery = "UPDATE tasks SET status = 'pending', completed_at = NULL WHERE parent_task_id = ?";
        $stmtSubtasks = $conn->prepare($subtaskQuery);
        $stmtSubtasks->bind_param("i", $task_id);
        $stmtSubtasks->execute();
        $stmtSubtasks->close();
    }

    // Step 5: Get parent_task_id first
    $selectParentQuery = "SELECT parent_task_id FROM tasks WHERE id = ?";
    $stmtSelect = $conn->prepare($selectParentQuery);
    $stmtSelect->bind_param("i", $task_id);
    $stmtSelect->execute();
    $stmtSelect->bind_result($parent_task_id);
    $stmtSelect->fetch();
    $stmtSelect->close();

    // Only update if it's a valid subtask
    if ($parent_task_id !== null) {
        $parentUpdateQuery = "UPDATE tasks SET position = 1 WHERE id = ?";
        $stmtParent = $conn->prepare($parentUpdateQuery);
        $stmtParent->bind_param("i", $parent_task_id);
        $stmtParent->execute();
        $stmtParent->close();
    }

    $conn->commit();

    echo json_encode(["success" => true, "message" => "Status updated", "completed_at" => $completed_at]);
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(["success" => false, "message" => "Failed to update", "error" => $e->getMessage()]);
}

$conn->close();
?>

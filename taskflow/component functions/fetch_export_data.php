<?php
header('Content-Type: application/json');
include '../../config db.php';
include '../../authentication/session.php';


$type = $_GET['type'] ?? '';
$userId = $_SESSION['user_id'];
$data = [];

switch ($type) {
    case 'todo':
        $stmt = $conn->prepare("SELECT id, title, status, difficulty_level FROM tasks WHERE user_id = ? AND status != 'completed' AND archived = 0");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $data = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        break;

    case 'completed':
        $stmt = $conn->prepare("SELECT id, title, status, difficulty_level FROM tasks WHERE user_id = ? AND status = 'completed' AND archived = 0");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $data = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        break;

    case 'categories':
        $stmt = $conn->prepare("SELECT id, name FROM tags WHERE user_id = ? AND archived = 0");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $data = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        break;

    case 'archive':
        $stmt = $conn->prepare("SELECT id, title, status FROM tasks WHERE user_id = ? AND archived = 1 UNION SELECT id, name as title, 'category' as status FROM tags WHERE user_id = ? AND archived = 1");
        $stmt->bind_param("ii", $userId, $userId);
        $stmt->execute();
        $data = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        break;
}

echo json_encode($data);
?>
<?php
include '../../config db.php';
include '../../authentication/session.php';

$userId = $_SESSION['user_id'];
function getTagIdsByNames($conn, $tagNames, $userId) {
    if (count($tagNames) === 0) return [];

    $placeholders = implode(',', array_fill(0, count($tagNames), '?'));
    $types = str_repeat('s', count($tagNames)) . 'i'; // all strings + user_id

    $sql = "SELECT id, name FROM tags WHERE name IN ($placeholders) AND user_id = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }

    $params = array_merge($tagNames, [$userId]);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();

    $result = $stmt->get_result();
    $tagIds = [];
    while ($row = $result->fetch_assoc()) {
        $tagIds[] = intval($row['id']);
    }
    $stmt->close();

    return $tagIds;
}


$inputJSON = file_get_contents('php://input');
$input = json_decode($inputJSON, true);

if (!$input) {
    echo json_encode(['success' => false, 'message' => 'Invalid JSON input']);
    exit;
}

$titleRaw = $input['title'] ?? '';
$titleTrimmed = trim($titleRaw);

if ($titleTrimmed === "") {
    echo json_encode(['success' => false, 'message' => 'Title cannot be empty or only spaces']);
    exit;
}
if ($titleRaw !== $titleTrimmed) {
    echo json_encode(['success' => false, 'message' => 'Title cannot start or end with spaces']);
    exit;
}

$title = $titleTrimmed;

$type = $input['type'] ?? '';
$id = $input['id'] ?? '';
$difficulty = floatval($input['difficulty'] ?? 0);
$tags = $input['tags'] ?? [];
if (!is_array($tags)) {
    $tags = [];
}

$table = ($type === 'task') ? 'tasks' : 'subtasks';

$conn->begin_transaction();

try {
    // 1. Update task/subtask title and difficulty
    $sql = "UPDATE tasks SET title = ?, difficulty_numeric = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sdi", $title, $difficulty, $id);
    $stmt->execute();
    $stmt->close();

    // Convert tag names to IDs
    $tagIds = getTagIdsByNames($conn, $tags, $userId);

    // 2. Delete existing tag relations for this task
    $deleteSql = "DELETE FROM task_tags WHERE task_id = ?";
    $delStmt = $conn->prepare($deleteSql);
    $delStmt->bind_param("i", $id);
    $delStmt->execute();
    $delStmt->close();

    // 3. Insert new tag relations if any tags exist
    if (count($tagIds) > 0) {
        $insertSql = "INSERT INTO task_tags (task_id, tag_id, user_id) VALUES (?, ?, ?)";
        $insStmt = $conn->prepare($insertSql);

        foreach ($tagIds as $tagId) {
            $insStmt->bind_param("iii", $id, $tagId, $userId);
            $insStmt->execute();
        }
        $insStmt->close();
    }

    $conn->commit();

    echo json_encode(['success' => true]);
    error_log("Raw title received: '" . $titleRaw . "'");
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>

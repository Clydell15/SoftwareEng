<?php
header("Content-Type: application/json");
include '../../config db.php';
include '../../authentication/session.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
    exit();
}

if (!isset($_POST['categoryId'])) {
    echo json_encode(["success" => false, "message" => "Category ID not provided"]);
    exit();
}

$categoryId = intval($_POST['categoryId']);
$userId = $_SESSION['user_id'];

// ✅ Check if task_tags connections exist
$checkStmt = $conn->prepare("SELECT COUNT(*) FROM task_tags WHERE tag_id = ? AND user_id = ?");
$checkStmt->bind_param("ii", $categoryId, $userId);
$checkStmt->execute();
$checkStmt->bind_result($usageCount);
$checkStmt->fetch();
$checkStmt->close();

// ✅ If any exist, delete them first
if ($usageCount > 0) {
    $deleteLinks = $conn->prepare("DELETE FROM task_tags WHERE tag_id = ? AND user_id = ?");
    $deleteLinks->bind_param("ii", $categoryId, $userId);
    $deleteLinks->execute();
    $deleteLinks->close();
}

// ✅ Then delete the category from tags
$stmt = $conn->prepare("DELETE FROM tags WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $categoryId, $userId);

if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "Category and connections deleted"]);
} else {
    echo json_encode(["success" => false, "message" => "Failed to delete category"]);
}

$stmt->close();
$conn->close();
?>

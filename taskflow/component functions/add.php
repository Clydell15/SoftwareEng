<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
header("Content-Type: application/json");

include '../../config db.php';
include '../../authentication/session.php';
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
date_default_timezone_set('Asia/Manila');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["success" => false, "message" => "Invalid request"]);
    exit();
}



$userId = $_SESSION['user_id'];

if (!empty($_POST['taskTitle']) && !empty($_POST['difficulty_numeric'])) { 
    handleTaskAddition($conn, $userId);
} elseif (!empty($_POST['categoryName'])) {
    handleCategoryAddition($conn, $userId);
} elseif (!empty($_POST['subtaskTitle']) && !empty($_POST['parentTaskId'])) {
    handleSubtaskAddition($conn, $userId);
} else {
    echo json_encode(["success" => false, "message" => "No valid input provided"]);
    exit();
}

// ========================
// 📌 FUNCTION: Add Task
// ========================
function handleTaskAddition($conn, $userId) {
    $titleRaw = $_POST['taskTitle'];
    $titleTrimmed = trim($titleRaw);

    if ($titleTrimmed === "") {
        echo json_encode(["success" => false, "message" => "Task title cannot be empty or only spaces"]);
        exit();
    }
    if ($titleRaw !== $titleTrimmed) {
        echo json_encode(["success" => false, "message" => "Task title cannot start or end with spaces"]);
        exit();
    }

    $title = $titleTrimmed;

    $difficultyNumeric = floatval($_POST['difficulty_numeric']);
    $rawTags = $_POST['tags'];
    $tags = json_decode($rawTags, true);

    // If json_decode fails (tags were not in valid JSON format), treat it as a comma-separated string
    if (!is_array($tags)) {
        $tags = explode(',', $rawTags);
        $tags = array_map('trim', $tags); // Remove any leading/trailing spaces
    }

    // Filter out empty tags
    $tags = array_filter($tags, fn($tag) => $tag !== "");

    $description = isset($_POST['description']) ? trim($_POST['description']) : "";  
    $dueDate = isset($_POST['due_date']) && !empty($_POST['due_date']) 
        ? $_POST['due_date'] 
        : date('Y-m-d H:i:s');  // Default to current date and time

    // Validate due date format to include both date and time (YYYY-MM-DD HH:MM:SS)
    if ($dueDate && !preg_match("/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/", $dueDate)) {
        echo json_encode(["success" => false, "message" => "Invalid due date format"]);
        exit();
    }

    $taskCount = 0;

    $checkStmt = $conn->prepare("SELECT COUNT(*) FROM tasks WHERE user_id = ?");
    $checkStmt->bind_param("i", $userId);
    $checkStmt->execute();
    $checkStmt->bind_result($taskCount);
    $checkStmt->fetch();
    $checkStmt->close();

    if ($taskCount > 0) {
        $stmt = $conn->prepare("UPDATE tasks SET position = position + 1 WHERE user_id = ?");
        $stmt->bind_param("i", $userId);
        if (!$stmt->execute()) {
            $stmt->close();
            echo json_encode(["success" => false, "message" => "Failed to shift tasks down"]);
            exit();
        }
        $stmt->close();
    }

    $stmt = $conn->prepare("INSERT INTO tasks (title, description, user_id, status, position, created_at, difficulty_numeric, due_date) 
                            VALUES (?, ?, ?, 'pending', 1, NOW(), ?, ?)");
    $stmt->bind_param("ssdss", $title, $description, $userId, $difficultyNumeric, $dueDate);

    if ($stmt->execute()) { 
        $taskId = $stmt->insert_id;  
        $stmt->close();
    
        if (!empty($tags)) {
            foreach ($tags as $tagName) {
                $checkStmt = $conn->prepare("SELECT id FROM tags WHERE name = ? AND user_id = ?");
                $checkStmt->bind_param("si", $tagName, $userId);
                $checkStmt->execute();
                $result = $checkStmt->get_result();
                $tag = $result->fetch_assoc();
                $checkStmt->close();
        
                if ($tag) {
                    $tagId = $tag['id'];
                    $linkStmt = $conn->prepare("INSERT INTO task_tags (task_id, tag_id, user_id) VALUES (?, ?, ?)");
                    $linkStmt->bind_param("iii", $taskId, $tagId, $userId);
                    if (!$linkStmt->execute()) {
                        echo json_encode(["success" => false, "message" => "Failed to associate tags with task"]);
                        exit();
                    }
                    $linkStmt->close();
                }
            }
        }
        
        echo json_encode(["success" => true, "task" => [
            "id" => $taskId,
            "title" => $title,
            "description" => $description,
            "status" => "pending",
            "position" => 1,
            "created_at" => date("Y-m-d H:i:s"),
            "difficulty_numeric" => $difficultyNumeric,
            "due_date" => $dueDate,
            "tags" => $tags
        ]]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to add task"]);
    }

    exit();
}






// ========================
// 📌 FUNCTION: Add Category
// ========================
function handleCategoryAddition($conn, $userId) {
    $categoryRaw = $_POST['categoryName'];
    $categoryTrimmed = trim($categoryRaw);

    if ($categoryTrimmed === "") {
        echo json_encode(["success" => false, "message" => "Category name cannot be empty or only spaces"]);
        exit();
    }
    if ($categoryRaw !== $categoryTrimmed) {
        echo json_encode(["success" => false, "message" => "Category name cannot start or end with spaces"]);
        exit();
    }

    $categoryName = $categoryTrimmed;
    $categoryId = null;
    // 🔹 Check if the tag already exists for this user
    $stmt = $conn->prepare("SELECT id FROM tags WHERE name = ? AND user_id = ?");
    $stmt->bind_param("si", $categoryName, $userId);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($categoryId);
        $stmt->fetch();
        $stmt->close();

        echo json_encode(["success" => false, "message" => "Tag already exists", "category_id" => $categoryId]);
        exit();
    }
    $stmt->close();

    // 🔹 Insert new tag if it doesn't exist
    $stmt = $conn->prepare("INSERT INTO tags (name, user_id) VALUES (?, ?)");
    $stmt->bind_param("si", $categoryName, $userId);

    if ($stmt->execute()) {
        $categoryId = $stmt->insert_id;
        $stmt->close();

        echo json_encode(["success" => true, "category" => [
            "id" => $categoryId,
            "name" => $categoryName,
            "task_count" => 0
        ]]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to add category"]);
    }

    exit();
}


// ========================
// 📌 FUNCTION: Add Subtask
// ========================
function handleSubtaskAddition($conn, $userId) {
    $subtaskTitleRaw = $_POST['subtaskTitle'];
    $subtaskTitleTrimmed = trim($subtaskTitleRaw);

    // Check for empty or only spaces
    if ($subtaskTitleTrimmed === "") {
        echo json_encode(["success" => false, "message" => "Subtask title cannot be empty or only spaces"]);
        exit();
    }
    // Check for leading/trailing spaces
    if ($subtaskTitleRaw !== $subtaskTitleTrimmed) {
        echo json_encode(["success" => false, "message" => "Subtask title cannot start or end with spaces"]);
        exit();
    }

    $subtaskTitle = $subtaskTitleTrimmed;

    $difficultyNumeric = floatval($_POST['difficulty_numeric']);
    
    $rawTags = $_POST['tags'];
    $tags = json_decode($rawTags, true);
    
    if (!is_array($tags)) {
        // If decoding fails, try treating it as a comma-separated string
        $tags = explode(',', $rawTags);
        $tags = array_map('trim', $tags);
    }
    
    $tags = array_filter($tags, fn($tag) => $tag !== ""); // Remove empty values

    $description = isset($_POST['description']) ? trim($_POST['description']) : "";  
    $parentTaskId = intval($_POST['parentTaskId']);

    // 🔹 Get the max position of existing subtasks for the parent task
    $stmt = $conn->prepare("SELECT COALESCE(MAX(position), 0) AS max_position FROM tasks WHERE parent_task_id = ?");
    $stmt->bind_param("i", $parentTaskId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();

    $newPosition = $row['max_position'] + 1; // Next position

    // 🔹 Insert new subtask at the last position
    $stmt = $conn->prepare("INSERT INTO tasks (title, description, user_id, parent_task_id, status, position, created_at, difficulty_numeric) 
                            VALUES (?, ?, ?, ?, 'pending', ?, NOW(), ?)");
    $stmt->bind_param("ssiiid", $subtaskTitle, $description, $userId, $parentTaskId, $newPosition, $difficultyNumeric);

   if ($stmt->execute()) { 
        $subtaskId = $stmt->insert_id;  
        $stmt->close();
    
        if (!empty($tags)) {
            foreach ($tags as $tagName) {
                // 🔹 Check if the tag already exists
                $checkStmt = $conn->prepare("SELECT id FROM tags WHERE name = ? AND user_id = ?");
                $checkStmt->bind_param("si", $tagName, $userId);
                $checkStmt->execute();
                $result = $checkStmt->get_result();
                $tag = $result->fetch_assoc();
                $checkStmt->close();
        
                if ($tag) { // ✅ If tag exists, link it
                    $tagId = $tag['id'];
        
                    // 🔹 Link tag to task
                    $linkStmt = $conn->prepare("INSERT INTO task_tags (task_id, tag_id, user_id) VALUES (?, ?, ?)");
                    $linkStmt->bind_param("iii", $subtaskId, $tagId, $userId);
                    if (!$linkStmt->execute()) {
                        echo json_encode(["success" => false, "message" => "Failed to associate tags with task"]);
                        exit();
                    }
                    $linkStmt->close();
                }
            }
        }

        echo json_encode(["success" => true, "subtask" => [
            "id" => $subtaskId,
            "title" => $subtaskTitle,
            "description" => $description,
            "status" => "pending",
            "position" => $newPosition,  // ✅ Now always at the bottom
            "parent_task_id" => $parentTaskId,
            "difficulty_numeric" => $difficultyNumeric,
            "tags" => $tags
        ]]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to add subtask"]);
    }

    exit();
}
?>

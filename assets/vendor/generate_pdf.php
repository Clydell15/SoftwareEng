<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
date_default_timezone_set('Asia/Manila');

error_log("PDF Generation Debug: Starting...");

include '../../config db.php';                 
include '../../authentication/session.php';      

if (!file_exists('tcpdf/tcpdf.php')) {  
    error_log("PDF Generation Error: TCPDF not found at: " . realpath('tcpdf/tcpdf.php'));
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'TCPDF library not found. Please install TCPDF first.']);
    exit;
}

require_once('tcpdf/tcpdf.php');  

// Get input data
$rawInput = file_get_contents('php://input');
error_log("PDF Generation Debug: Raw input - " . $rawInput);

$input = json_decode($rawInput, true);
error_log("PDF Generation Debug: Decoded input - " . print_r($input, true));

$userId = $_SESSION['user_id'] ?? null;
error_log("PDF Generation Debug: User ID - " . $userId);

// Validate input
if (!$input || !isset($input['pages']) || empty($input['pages'])) {
    error_log("PDF Generation Error: Invalid request data");
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Invalid request data']);
    exit;
}

if (!$userId) {
    error_log("PDF Generation Error: No user ID in session");
    http_response_code(401);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'User not authenticated']);
    exit;
}

try {
    error_log("PDF Generation Debug: Creating TCPDF instance...");
    
    // Create new PDF document
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

    // Set document information
    $pdf->SetCreator('TaskFlow');
    $pdf->SetAuthor('TaskFlow User');
    $pdf->SetTitle('TaskFlow Manager');
    $pdf->SetSubject('Task Management Export');

    // Set default header data
    $pdf->SetHeaderData('', 0, 'TaskFlow Manager', 'Generated on ' . date('Y-m-d') . ' at ' . date('g:i A'));

    // Set header and footer fonts
    $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
    $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

    // Set default monospaced font
    $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

    // Set margins
    $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
    $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
    $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

    // Set auto page breaks
    $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

    // Set font
    $pdf->SetFont('helvetica', '', 10);

    // Add a page
    $pdf->AddPage();

    error_log("PDF Generation Debug: PDF setup complete, generating content...");

    // Generate content for each selected page
    $html = '<h1 style="color: #2c3e50; text-align: center;">TaskFlow Manager</h1>';
    $html .= '<p style="text-align: center; color: #7f8c8d; font-size: 1.1em; margin-bottom: 0;">Prepared by: ' . htmlspecialchars($user['email']) . '</p>';
    $html .= '<p style="text-align: center; color: #7f8c8d;">Generated on: ' . date('F j, Y \a\t g:i A') . '</p>';
    $html .= '<hr>';

    foreach ($input['pages'] as $pageType) {
        error_log("PDF Generation Debug: Generating content for page type: " . $pageType);
        $html .= generatePageContent($pageType, $input, $conn, $userId);
    }

    error_log("PDF Generation Debug: Content generated, writing to PDF...");

    // Write the HTML content
    $pdf->writeHTML($html, true, false, true, false, '');

    error_log("PDF Generation Debug: Content written, outputting PDF...");

    // Close and output PDF document
    $pdfContent = $pdf->Output('TaskFlow_Export.pdf', 'S');

    // Set headers for PDF download
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="TaskFlow_Export_' . date('Y-m-d') . '.pdf"');
    header('Content-Length: ' . strlen($pdfContent));

    error_log("PDF Generation Debug: PDF generated successfully, size: " . strlen($pdfContent) . " bytes");

    echo $pdfContent;

} catch (Exception $e) {
    error_log("PDF Generation Error: " . $e->getMessage() . " - " . $e->getTraceAsString());
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'PDF generation failed: ' . $e->getMessage()]);
}

// Utility function to safely execute queries
function executeQuery($conn, $sql, $params = [], $types = '') {
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        error_log("Query preparation failed: " . $conn->error);
        return false;
    }
    
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    if (!$stmt->execute()) {
        error_log("Query execution failed: " . $stmt->error);
        $stmt->close();
        return false;
    }
    
    $result = $stmt->get_result();
    $data = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    
    return $data;
}

function generatePageContent($pageType, $options, $conn, $userId) {
    $html = '';
    
    switch ($pageType) {
        case 'todo':
            $html .= generateTodoContent($options, $conn, $userId);
            break;
        case 'completed':
            $html .= generateCompletedContent($options, $conn, $userId);
            break;
        case 'categories':
            $html .= generateCategoriesContent($options, $conn, $userId);
            break;
        case 'archive':
            $html .= generateArchiveContent($options, $conn, $userId);
            break;
    }
    
    return $html;
}

function generateTodoContent($options, $conn, $userId) {
    $html = '<h2 style="color: #3498db; margin-top: 20px;">To-Do Tasks</h2>';
    
    error_log("generateTodoContent: Starting for user $userId");
    
    // Get selected task IDs
    $selectedIds = $options['selected_items']['todo'] ?? [];
    
    // Build SQL query
    if (empty($selectedIds)) {
        $sql = "SELECT 
                    t.id, t.title, t.description, t.status, t.difficulty_level, 
                    t.due_date, t.created_at,
                    GROUP_CONCAT(tags.name SEPARATOR ', ') as tag_names
                FROM tasks t
                LEFT JOIN task_tags tt ON t.id = tt.task_id
                LEFT JOIN tags ON tt.tag_id = tags.id
                WHERE t.user_id = ? AND t.status != 'completed' AND t.archived = 0
                GROUP BY t.id
                ORDER BY t.created_at DESC";
        $tasks = executeQuery($conn, $sql, [$userId], 'i');
    } else {
        // Validate and filter selected IDs
        $selectedIds = array_filter(array_map('intval', $selectedIds), function($id) { return $id > 0; });
        
        if (empty($selectedIds)) {
            $html .= '<p style="color: #7f8c8d; font-style: italic;">No valid task IDs provided.</p>';
            return $html;
        }
        
        $placeholders = str_repeat('?,', count($selectedIds) - 1) . '?';
        $sql = "SELECT 
                    t.id, t.title, t.description, t.status, t.difficulty_level, 
                    t.due_date, t.created_at,
                    GROUP_CONCAT(tags.name SEPARATOR ', ') as tag_names
                FROM tasks t
                LEFT JOIN task_tags tt ON t.id = tt.task_id
                LEFT JOIN tags ON tt.tag_id = tags.id
                WHERE t.user_id = ? AND t.id IN ($placeholders) AND t.status != 'completed' AND t.archived = 0
                GROUP BY t.id
                ORDER BY t.created_at DESC";
        
        $types = 'i' . str_repeat('i', count($selectedIds));
        $params = array_merge([$userId], $selectedIds);
        $tasks = executeQuery($conn, $sql, $params, $types);
    }
    
    if ($tasks === false) {
        $html .= '<p style="color: #e74c3c;">Database error occurred while fetching tasks.</p>';
        return $html;
    }
    
    if (empty($tasks)) {
        $html .= '<p style="color: #7f8c8d; font-style: italic;">No to-do tasks found.</p>';
        return $html;
    }
    
    error_log("generateTodoContent: Found " . count($tasks) . " tasks");
    
    // Determine which columns are included
    $includeDifficulty = $options['include_difficulty'] ?? true;
    $includeDueDates = $options['include_due_dates'] ?? true;
    $includeTags = $options['include_tags'] ?? true;
    $dynamicCols = 0;
    if ($includeDifficulty) $dynamicCols++;
    if ($includeDueDates) $dynamicCols++;
    if ($includeTags) $dynamicCols++;
    $baseCols = 2;
    $totalCols = $baseCols + $dynamicCols;
    $baseWidth = $dynamicCols === 0 ? 50 : round(100 / $totalCols, 2);
    $dynamicWidth = $dynamicCols > 0 ? round(100 / $totalCols, 2) : 0;
    $headerBg = 'rgb(227, 117, 105)';
    $html .= '<table border="1" cellpadding="5" cellspacing="0" style="width: 100%; border-collapse: collapse;">';
    $html .= '<thead style="background-color: ' . $headerBg . ';">';
    $html .= '<tr>';
    $html .= '<th style="width: ' . $baseWidth . '%; background-color: ' . $headerBg . ';">Task</th>';
    $html .= '<th style="width: ' . $baseWidth . '%; background-color: ' . $headerBg . ';">Status</th>';
    if ($includeDifficulty) {
        $html .= '<th style="width: ' . $dynamicWidth . '%; background-color: ' . $headerBg . ';">Difficulty</th>';
    }
    if ($includeDueDates) {
        $html .= '<th style="width: ' . $dynamicWidth . '%; background-color: ' . $headerBg . ';">Due Date</th>';
    }
    if ($includeTags) {
        $html .= '<th style="width: ' . $dynamicWidth . '%; background-color: ' . $headerBg . ';">Tags</th>';
    }
    $html .= '</tr>';
    $html .= '</thead><tbody>';
    
    foreach ($tasks as $task) {
        $html .= '<tr>';
        $html .= '<td>';
        $html .= '<strong>' . htmlspecialchars($task['title']) . '</strong>';
        if (!empty($task['description'])) {
            $html .= '<br><small style="color: #7f8c8d;">' . htmlspecialchars($task['description']) . '</small>';
        }
        
        // Include subtasks if option is enabled
        if ($options['include_subtasks'] ?? true) {
            // Check if subtasks table exists, if not, skip this section
            $checkSubtasksTable = executeQuery($conn, "SHOW TABLES LIKE 'subtasks'", [], '');
            
            if ($checkSubtasksTable !== false && !empty($checkSubtasksTable)) {
                $subtaskSql = "SELECT title, is_completed FROM subtasks WHERE task_id = ? ORDER BY created_at";
                $subtasks = executeQuery($conn, $subtaskSql, [$task['id']], 'i');
                
                if ($subtasks !== false && !empty($subtasks)) {
                    $html .= '<br><strong>Subtasks:</strong>';
                    $html .= '<ul style="margin: 5px 0; padding-left: 20px;">';
                    foreach ($subtasks as $subtask) {
                        $checkmark = $subtask['is_completed'] ? '[X]' : '[ ]';
                        $html .= '<li>' . $checkmark . ' ' . htmlspecialchars($subtask['title']) . '</li>';
                    }
                    $html .= '</ul>';
                }
            } else {
                // If no subtasks table, check for parent_task_id in tasks table
                $subtaskSql = "SELECT title, status FROM tasks WHERE parent_task_id = ? ORDER BY created_at";
                $subtasks = executeQuery($conn, $subtaskSql, [$task['id']], 'i');
                
                if ($subtasks !== false && !empty($subtasks)) {
                    $html .= '<br><strong>Subtasks:</strong>';
                    $html .= '<ul style="margin: 5px 0; padding-left: 20px;">';
                    foreach ($subtasks as $subtask) {
                        $checkmark = $subtask['status'] === 'completed' ? '[X]' : '[ ]';
                        $html .= '<li>' . $checkmark . ' ' . htmlspecialchars($subtask['title']) . '</li>';
                    }
                    $html .= '</ul>';
                }
            }
        }
        
        $html .= '</td>';
        
        // Status
        $html .= '<td>' . ucfirst(htmlspecialchars($task['status'])) . '</td>';
        
        // Difficulty
        if ($includeDifficulty) {
            $difficulty = 'Not set';
            if (isset($task['difficulty_numeric']) && $task['difficulty_numeric'] > 0) {
                $difficulty = number_format($task['difficulty_numeric'], 1);
            } elseif (isset($task['difficulty_level']) && $task['difficulty_level'] > 0) {
                switch ($task['difficulty_level']) {
                    case 1: $difficulty = 'Easy'; break;
                    case 2: $difficulty = 'Medium'; break;
                    case 3: $difficulty = 'Hard'; break;
                }
            }
            $html .= '<td>' . $difficulty . '</td>';
        }
        // Due Date
        if ($includeDueDates) {
            $dueDate = $task['due_date'] ? date('M j, Y', strtotime($task['due_date'])) : 'No due date';
            $html .= '<td>' . $dueDate . '</td>';
        }
        // Tags
        if ($includeTags) {
            $tags = $task['tag_names'] ? htmlspecialchars($task['tag_names']) : 'No tags';
            $html .= '<td>' . $tags . '</td>';
        }
        $html .= '</tr>';
    }
    $html .= '</tbody></table>';
    return $html;
}

function generateCompletedContent($options, $conn, $userId) {
    $html = '<h2 style="color: #27ae60; margin-top: 20px;">Completed Tasks</h2>';
    error_log("generateCompletedContent: Starting for user $userId");
    $selectedIds = $options['selected_items']['completed'] ?? [];
    // Only use columns that are likely to exist
    if (empty($selectedIds)) {
        $sql = "SELECT t.id, t.title, t.description, t.status, t.difficulty_level, t.due_date, t.created_at, t.completed_at, GROUP_CONCAT(tags.name SEPARATOR ', ') as tag_names FROM tasks t LEFT JOIN task_tags tt ON t.id = tt.task_id LEFT JOIN tags ON tt.tag_id = tags.id WHERE t.user_id = ? AND t.status = 'completed' AND t.archived = 0 GROUP BY t.id ORDER BY t.completed_at DESC";
        $tasks = executeQuery($conn, $sql, [$userId], 'i');
    } else {
        $selectedIds = array_filter(array_map('intval', $selectedIds), function($id) { return $id > 0; });
        if (empty($selectedIds)) {
            $html .= '<p style="color: #7f8c8d; font-style: italic;">No valid task IDs provided.</p>';
            return $html;
        }
        $placeholders = str_repeat('?,', count($selectedIds) - 1) . '?';
        $sql = "SELECT t.id, t.title, t.description, t.status, t.difficulty_level, t.due_date, t.created_at, t.completed_at, GROUP_CONCAT(tags.name SEPARATOR ', ') as tag_names FROM tasks t LEFT JOIN task_tags tt ON t.id = tt.task_id LEFT JOIN tags ON tt.tag_id = tags.id WHERE t.user_id = ? AND t.id IN ($placeholders) AND t.status = 'completed' AND t.archived = 0 GROUP BY t.id ORDER BY t.completed_at DESC";
        $types = 'i' . str_repeat('i', count($selectedIds));
        $params = array_merge([$userId], $selectedIds);
        $tasks = executeQuery($conn, $sql, $params, $types);
    }
    if ($tasks === false) {
        $html .= '<p style="color: #e74c3c;">Database error occurred while fetching completed tasks.</p>';
        return $html;
    }
    if (empty($tasks)) {
        $html .= '<p style="color: #7f8c8d; font-style: italic;">No completed tasks found.</p>';
        return $html;
    }
    $includeDifficulty = $options['include_difficulty'] ?? true;
    $dynamicCols = $includeDifficulty ? 1 : 0;
    $baseCols = 2;
    $totalCols = $baseCols + $dynamicCols;
    $baseWidth = $dynamicCols === 0 ? 50 : round(100 / $totalCols, 2);
    $dynamicWidth = $dynamicCols > 0 ? round(100 / $totalCols, 2) : 0;
    $headerBg = 'rgb(93, 169, 125)';
    $html .= '<table border="1" cellpadding="5" cellspacing="0" style="width: 100%; border-collapse: collapse;">';
    $html .= '<thead style="background-color: ' . $headerBg . ';">';
    $html .= '<tr>';
    $html .= '<th style="width: ' . $baseWidth . '%; background-color: ' . $headerBg . ';">Task</th>';
    $html .= '<th style="width: ' . $baseWidth . '%; background-color: ' . $headerBg . ';">Completed Date</th>';
    if ($includeDifficulty) {
        $html .= '<th style="width: ' . $dynamicWidth . '%; background-color: ' . $headerBg . ';">Difficulty</th>';
    }
    $html .= '</tr>';
    $html .= '</thead><tbody>';
    foreach ($tasks as $task) {
        $html .= '<tr>';
        $html .= '<td>';
        $html .= '<strong>' . htmlspecialchars($task['title']) . '</strong>';
        if (!empty($task['description'])) {
            $html .= '<br><small style="color: #7f8c8d;">' . htmlspecialchars($task['description']) . '</small>';
        }
        $html .= '</td>';
        // Completed Date
        $completedDate = isset($task['completed_at']) && $task['completed_at'] ? date('M j, Y g:i A', strtotime($task['completed_at'])) : (isset($task['created_at']) && $task['created_at'] ? date('M j, Y g:i A', strtotime($task['created_at'])) : 'Unknown');
        $html .= '<td>' . $completedDate . '</td>';
        // Difficulty
        if ($includeDifficulty) {
            $difficulty = 'Not set';
            if (isset($task['difficulty_level']) && $task['difficulty_level'] > 0) {
                switch ($task['difficulty_level']) {
                    case 1: $difficulty = 'Easy'; break;
                    case 2: $difficulty = 'Medium'; break;
                    case 3: $difficulty = 'Hard'; break;
                }
            }
            $html .= '<td>' . $difficulty . '</td>';
        }
        $html .= '</tr>';
    }
    $html .= '</tbody></table>';
    return $html;
}

function generateCategoriesContent($options, $conn, $userId) {
    $html = '<h2 style="color: #9b59b6; margin-top: 20px;">Categories</h2>';
    
    error_log("generateCategoriesContent: Starting for user $userId");
    
    $selectedIds = $options['selected_items']['categories'] ?? [];
    
    if (empty($selectedIds)) {
        $sql = "SELECT id, name FROM tags WHERE user_id = ? AND archived = 0 ORDER BY name";
        $categories = executeQuery($conn, $sql, [$userId], 'i');
    } else {
        $selectedIds = array_filter(array_map('intval', $selectedIds), function($id) { return $id > 0; });
        
        if (empty($selectedIds)) {
            $html .= '<p style="color: #7f8c8d; font-style: italic;">No valid category IDs provided.</p>';
            return $html;
        }
        
        $placeholders = str_repeat('?,', count($selectedIds) - 1) . '?';
        $sql = "SELECT id, name FROM tags WHERE user_id = ? AND id IN ($placeholders) AND archived = 0 ORDER BY name";
        
        $types = 'i' . str_repeat('i', count($selectedIds));
        $params = array_merge([$userId], $selectedIds);
        $categories = executeQuery($conn, $sql, $params, $types);
    }
    
    if ($categories === false) {
        $html .= '<p style="color: #e74c3c;">Database error occurred while fetching categories.</p>';
        return $html;
    }
    
    if (empty($categories)) {
        $html .= '<p style="color: #7f8c8d; font-style: italic;">No categories found.</p>';
        return $html;
    }
    
    // Dynamic column widths: if only one column, 100%; if two, 50%/50%; else default to 30/70
    $colCount = 2;
    $colWidths = [50, 50];
    if (isset($options['categories_columns']) && is_array($options['categories_columns'])) {
        $colCount = count($options['categories_columns']);
        if ($colCount === 1) {
            $colWidths = [100];
        } else if ($colCount === 2) {
            $colWidths = [50, 50];
        } else {
            // If more columns are added in the future, distribute evenly
            $colWidths = array_fill(0, $colCount, round(100 / $colCount, 2));
        }
    } else {
        // Default: 30% for name, 70% for tasks
        $colWidths = [50, 50];
    }
    $html .= '<table border="1" cellpadding="5" cellspacing="0" style="width: 100%; border-collapse: collapse;">';
    $html .= '<thead style="background-color: #f4ecf7;">';
    $html .= '<tr>';
    $html .= '<th style="width: ' . $colWidths[0] . '%;">Category Name</th>';
    $html .= '<th style="width: ' . $colWidths[1] . '%;">Associated Tasks</th>';
    $html .= '</tr>';
    $html .= '</thead><tbody>';
    
    foreach ($categories as $category) {
        $html .= '<tr>';
        $html .= '<td><strong>' . htmlspecialchars($category['name']) . '</strong></td>';
        
        // Get tasks for this category
        $taskSql = "SELECT t.title, t.status 
                   FROM tasks t 
                   JOIN task_tags tt ON t.id = tt.task_id 
                   WHERE tt.tag_id = ? AND t.archived = 0 
                   ORDER BY t.status, t.title";
        $tasks = executeQuery($conn, $taskSql, [$category['id']], 'i');
        
        $html .= '<td>';
        if ($tasks === false || empty($tasks)) {
            $html .= '<em style="color: #7f8c8d;">No tasks associated</em>';
        } else {
            $html .= '<ul style="margin: 0; padding-left: 20px;">';
            foreach ($tasks as $task) {
                $statusIcon = $task['status'] === 'completed' ? '[COMPLETED]' : '[PENDING]';
                $html .= '<li>' . $statusIcon . ' ' . htmlspecialchars($task['title']) . '</li>';
            }
            $html .= '</ul>';
        }
        $html .= '</td>';
        
        $html .= '</tr>';
    }
    
    $html .= '</tbody></table>';
    return $html;
}

function generateArchiveContent($options, $conn, $userId) {
    $html = '<h2 style="color: #e67e22; margin-top: 20px;">Archive</h2>';
    
    error_log("generateArchiveContent: Starting for user $userId");
    
    // Get archived tasks
    $taskSql = "SELECT title, description FROM tasks WHERE user_id = ? AND archived = 1 ORDER BY id DESC";
    $archivedTasks = executeQuery($conn, $taskSql, [$userId], 'i');
    
    // Get archived categories
    $categorySql = "SELECT name FROM tags WHERE user_id = ? AND archived = 1 ORDER BY name";
    $archivedCategories = executeQuery($conn, $categorySql, [$userId], 'i');
    
    if (($archivedTasks === false || empty($archivedTasks)) &&
        ($archivedCategories === false || empty($archivedCategories))) {
        $html .= '<p style="color: #7f8c8d; font-style: italic;">No archived items found.</p>';
        return $html;
    }

    $html .= '<table border="1" cellpadding="8" cellspacing="0" style="width: 100%; border-collapse: collapse; margin-top: 20px;">';
    $html .= '<thead><tr>';
    $html .= '<th style="width: 50%; background-color: #fbeee0; color: #d35400;">Archived Tasks</th>';
    $html .= '<th style="width: 50%; background-color: #fbeee0; color: #d35400;">Archived Categories</th>';
    $html .= '</tr></thead><tbody><tr>';

    // Archived Tasks column
    $html .= '<td valign="top">';
    if ($archivedTasks !== false && !empty($archivedTasks)) {
        $html .= '<ul style="padding-left: 20px; margin: 0;">';
        foreach ($archivedTasks as $task) {
            $html .= '<li><strong>' . htmlspecialchars($task['title']) . '</strong>';
            if (!empty($task['description'])) {
                $html .= ' - ' . htmlspecialchars($task['description']);
            }
            $html .= '</li>';
        }
        $html .= '</ul>';
    } else {
        $html .= '<em style="color: #7f8c8d;">No archived tasks</em>';
    }
    $html .= '</td>';

    // Archived Categories column
    $html .= '<td valign="top">';
    if ($archivedCategories !== false && !empty($archivedCategories)) {
        $html .= '<ul style="padding-left: 20px; margin: 0;">';
        foreach ($archivedCategories as $category) {
            $html .= '<li><strong>' . htmlspecialchars($category['name']) . '</strong></li>';
        }
        $html .= '</ul>';
    } else {
        $html .= '<em style="color: #7f8c8d;">No archived categories</em>';
    }
    $html .= '</td>';

    $html .= '</tr></tbody></table>';
    return $html;
}
?>
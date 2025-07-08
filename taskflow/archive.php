<?php
include '../config db.php';
include '../authentication/session.php';

$userId = $_SESSION['user_id'];

// Fetch archived tasks and categories
$archivedTasks = $conn->query("SELECT * FROM tasks WHERE user_id = $userId AND archived = 1")->fetch_all(MYSQLI_ASSOC);
$archivedCategories = $conn->query("SELECT * FROM tags WHERE user_id = $userId AND archived = 1")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html data-bs-theme="light">
<head>
    <title>Archive | TaskFlow</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/pomodoro.css">
</head>
<body>
<div class="d-flex h-100">
    <?php include '../taskflow/component functions/sidebar.php'; ?>

    <div class="content flex-grow-1 p-3 d-flex flex-column">
        <div class="header-container d-flex justify-content-between align-items-center">
            <h2>Archive</h2>
            <a href="settings.php" class="btn btn-outline-secondary">Back to Settings</a>
        </div>
        <div class="task-view-pane mx-auto mt-4 p-3 rounded" style="height: 100vh;">
            <div class="d-flex flex-wrap gap-3 h-100">
                <!-- Archived Tasks (Left) -->
                <div class="card" style="flex: 1 1 calc(50% - 0.75rem); height: 100%; display: flex; flex-direction: column;">
                    <div class="px-4 py-3 border-bottom bg-light text-center">
                        <h3 class="mb-0 fw-semibold">Archived Tasks</h3>
                    </div>
                    <div class="card-body overflow-auto">
                        <?php if (empty($archivedTasks)): ?>
                            <p class="text-center text-muted">No archived tasks.</p>
                        <?php else: ?>
                            <ul class="list-group mb-4">
                                <?php foreach ($archivedTasks as $task): ?>
                                    <li class="list-group-item d-flex flex-column align-items-start">
                                        <div>
                                            <strong><?= htmlspecialchars($task['title']) ?></strong>
                                            <span class="badge bg-secondary"><?= htmlspecialchars($task['status']) ?></span>
                                            <span class="badge bg-info"><?= htmlspecialchars($task['difficulty_level']) ?></span>
                                            <?php if (!empty($task['due_date'])): ?>
                                                <span class="badge bg-light text-dark"><?= htmlspecialchars($task['due_date']) ?></span>
                                            <?php endif; ?>
                                        </div>
                                        <?php if (!empty($task['description'])): ?>
                                            <small class="text-muted"><?= htmlspecialchars($task['description']) ?></small>
                                        <?php endif; ?>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                </div>
                <!-- Archived Categories (Right) -->
                <div class="card" style="flex: 1 1 calc(50% - 0.75rem); height: 100%; display: flex; flex-direction: column;">
                    <div class="px-4 py-3 border-bottom bg-light text-center">
                        <h3 class="mb-0 fw-semibold">Archived Categories</h3>
                    </div>
                    <div class="card-body overflow-auto">
                        <?php if (empty($archivedCategories)): ?>
                            <p class="text-center text-muted">No archived categories.</p>
                        <?php else: ?>
                            <ul class="list-group">
                                <?php foreach ($archivedCategories as $cat): ?>
                                    <li class="list-group-item"><?= htmlspecialchars($cat['name']) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../taskflow/component functions/modals.php'; ?>
<script>
    var userPomodoroSettings = {
        pomodoro: <?php echo json_encode($user['pomodoro_time']); ?>,
        shortBreak: <?php echo json_encode($user['shortbreak_time']); ?>,
        longBreak: <?php echo json_encode($user['longbreak_time']); ?>
    };
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/javascript/script.js"></script>
<script src="../assets/javascript/pomodoro.js"></script>
<script src="../assets/javascript/pdfexport.js"></script>
</body>
</html>
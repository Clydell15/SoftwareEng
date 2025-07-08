<?php
include '../config db.php';
include '../authentication/session.php';
include '../taskflow/component functions/query.php';
include '../taskflow/component functions/fetch_category.php';
?>

<!DOCTYPE html>
<html data-bs-theme="light">
<head>
    <title>TaskFlow Dashboard</title>
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
            <h2>Categories</h2>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCategoryModal">+ Add Category</button>
        </div>
        <div class="task-view-pane mx-auto mt-4 p-3 rounded">
        <?php if (empty($categories)): ?>
                <p class="text-center">No categories available</p>
            <?php else: ?>
                <ul class="list-group" id="category-list">
                <?php foreach ($categories as $category): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <?= htmlspecialchars($category['name']) ?>
                        <button class="delete-category-btn btn btn-sm btn-danger" data-category-id="<?= $category['id'] ?>">
                            <i class="bi bi-trash"></i>
                        </button>
                    </li>
                <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </div>
</div>



<!-- Add Task Modal -->
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

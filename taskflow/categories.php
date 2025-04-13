<?php
include '../config db.php';
include '../authentication/session.php';
// include '../taskflow/components/query.php';
?>

<!DOCTYPE html>
<html data-bs-theme="light">
<head>
    <title>TaskFlow Dashboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="d-flex h-100">
    <?php include '../taskflow/component functions/sidebar.php'; ?>


    <div class="content flex-grow-1 p-3 d-flex flex-column">
        <div class="header-container d-flex justify-content-between align-items-center">
            <h2>Categories</h2>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addTaskModal">+ Add Category</button>
        </div>
        <div class="task-view-pane mx-auto mt-4 p-3 rounded">
        
        </div>
    </div>
</div>



<!-- Add Task Modal -->
<?php include '../taskflow/component functions/modals.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/script.js"></script>
</body>
</html>

<?php
include '../config db.php';
include '../taskflow/component functions/dashboard_display.php';
include '../authentication/session.php';

?>

<!DOCTYPE html>
<html data-bs-theme="light">
<head>
    <title>TaskFlow Dashboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/pomodoro.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
</head>
<body>
<div class="d-flex h-100">
    <?php include '../taskflow/component functions/sidebar.php'; ?>


    <div class="content flex-grow-1 p-3 d-flex flex-column">
        <div class="header-container d-flex justify-content-between align-items-center">
            <h2>Dashboard</h2>
            
        </div>
        <div class="task-view-pane mx-auto mt-4 p-3 rounded" style="height: calc(100vh - 2rem); overflow: hidden;">

            <div class="d-flex flex-wrap gap-3 h-100">
                <!-- Upper Left: Task Overview -->
                <div class="card" style="flex: 1 1 calc(50% - 0.75rem); height: 48%;">
                    <!-- Header -->
                    <div class="px-4 py-3 border-bottom bg-light text-center">
                        <h3 class="mb-0 fw-semibold">Task Overview</h3>
                    </div>
                    <!-- Body -->
                    <div class="card-body d-flex flex-column justify-content-center align-items-center text-center h-100">
                        <div class="d-flex gap-5">
                            <!-- Completed Tasks -->
                            <div class="d-flex flex-column align-items-center">
                                <div class="rounded-circle bg-success text-white d-flex justify-content-center align-items-center mb-2 shadow"
                                    style="width: 120px; height: 120px; font-size: 42px; font-weight: bold;">
                                    <?= $completedCount ?>
                                </div>
                                <span class="fs-6 text-muted">Completed</span>
                            </div>
                            <!-- Pending Tasks -->
                            <div class="d-flex flex-column align-items-center">
                                <div class="rounded-circle bg-warning text-dark d-flex justify-content-center align-items-center mb-2 shadow"
                                    style="width: 120px; height: 120px; font-size: 42px; font-weight: bold;">
                                    <?= $pendingCount ?>
                                </div>
                                <span class="fs-6 text-muted">Pending</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Upper Right: Pomodoro -->
                <div class="card" style="flex: 1 1 calc(50% - 0.75rem); height: 48%;">
                    <div class="px-4 py-3 border-bottom bg-light text-center">
                        <h3 class="mb-0 fw-semibold">Pomodoro</h3>
                    </div>
                    <div class="d-flex justify-content-around text-center h-100 align-items-center flex-wrap">
                        <div>
                            <div class="progress-ring-container position-relative mb-2">
                                <svg class="progress-ring" width="120" height="120">
                                    <circle class="progress-ring-bg" cx="60" cy="60" r="54" stroke-width="12" />
                                    <circle class="progress-ring-fill" cx="60" cy="60" r="54" stroke-width="12"
                                        stroke-dasharray="<?= 2 * pi() * 54 ?>"
                                        stroke-dashoffset="<?= (1 - $progressPercent / 100) * 2 * pi() * 54 ?>" />
                                </svg>
                                <div class="position-absolute top-50 start-50 translate-middle fs-5 fw-bold">
                                    <?= $sessions ?> / <?= $goal ?>
                                </div>
                            </div>
                            <div class="text-muted small">Today's Progress</div>
                        </div>

                        <div>
                            <div class="rounded-circle bg-warning-subtle text-warning-emphasis d-flex align-items-center justify-content-center" style="width: 80px; height: 80px; font-size: 1.5rem;">
                                <?= $userdash['short_breaks'] ?>
                            </div>
                            <div class="mt-2">Short Breaks</div>
                        </div>
                        <div>
                            <div class="rounded-circle bg-info-subtle text-info-emphasis d-flex align-items-center justify-content-center" style="width: 80px; height: 80px; font-size: 1.5rem;">
                                <?= $userdash['long_breaks'] ?>
                            </div>
                            <div class="mt-2">Long Breaks</div>
                        </div>
                    </div>

                    <div class="text-center mt-3 fw-medium text-secondary">
                        <?= $message ?>
                    </div>
                    <br/>
                </div>


                <!-- Lower Full Width: Upcoming Deadlines -->
                <div class="card w-100" style="height: calc(48% - 0.75rem);">
                    <!-- Header -->
                    <div class="px-4 py-3 border-bottom bg-light">
                        <h3 class="mb-0 fw-semibold">Upcoming Deadlines</h3>
                    </div>

                    <!-- Body -->
                    <div class="card-body overflow-auto">
                        <ul class="list-group">
                            <?php if (empty($upcomingTasks)): ?>
                                <li class="list-group-item text-muted">No upcoming deadlines.</li>
                            <?php else: ?>
                                <?php foreach ($upcomingTasks as $task): 
                                    $dueDate = new DateTime($task['due_date'], new DateTimeZone('Asia/Manila'));
                                    $now = new DateTime('now', new DateTimeZone('Asia/Manila'));

                                    $diffSeconds = $dueDate->getTimestamp() - $now->getTimestamp();

                                    if ($dueDate < $now) {
                                        $label = 'Overdue';
                                        $badgeClass = 'bg-danger';
                                    } else {
                                        $interval = $now->diff($dueDate);
                                        $diffDays = $interval->d;
                                        $diffHours = $interval->h;
                                        $diffMinutes = $interval->i;

                                        if ($diffDays === 0) {
                                            $label = "Today ({$diffHours}h {$diffMinutes}m left)";
                                            $badgeClass = 'bg-warning text-dark';
                                        } elseif ($diffDays === 1) {
                                            $label = 'Tomorrow';
                                            $badgeClass = 'bg-info text-dark';
                                        } else {
                                            $label = "In $diffDays days";
                                            $badgeClass = 'bg-secondary';
                                        }
                                    }

                                    // Change this to include both date and time
                                    $timeFormatted = $dueDate->format('M j, Y g:i A'); 

                                ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <?= htmlspecialchars($task['title']) ?>
                                        <div class="d-flex align-items-center gap-2">
                                            <small class="text-muted"><?= $timeFormatted ?></small>
                                            <span class="badge <?= $badgeClass ?>"><?= $label ?></span>
                                        </div>
                                    </li>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>



<!-- Add Task Modal -->
<?php include '../taskflow/component functions/modals.php'; ?>
<script>
    // Pass PHP values into JavaScript
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

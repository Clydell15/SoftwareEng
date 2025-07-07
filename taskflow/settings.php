<?php
include '../config db.php';
include '../authentication/session.php';

$user = $_SESSION['user'];
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
            <h2>Settings</h2>
        </div>
        <div class="task-view-pane mx-auto mt-4 p-3 rounded" style="height: 100vh;">
            <div class="d-flex flex-wrap gap-3 h-100">


                <div class="card" style="flex: 1 1 calc(50% - 0.75rem); height: 100%; display: flex; flex-direction: column;">

                    <div class="px-4 py-3 border-bottom bg-light text-center">
                        <h3 class="mb-0 fw-semibold">Account Settings</h3>
                    </div>

                    <div class="card-body d-flex flex-column justify-content-between" style="flex: 1;">
                        <form id="account-settings-form" action="../taskflow/component functions/update_account.php" method="POST" class="d-flex flex-column gap-4 flex-grow-1">
                            <div>
                                <label for="email" class="form-label">Email</label>
                                <input type="email" id="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" readonly required>
                            </div>

                            <div>
                                <label for="old_password" class="form-label">Password</label>
                                <input type="password" id="old_password" name="old_password" class="form-control" value="" placeholder="Input password"readonly required>
                            </div>

                            <div id="new_password_div" class="d-none">
                                <label for="new_password" class="form-label">New Password</label>
                                <input type="password" id="new_password" name="new_password" class="form-control" readonly>
                            </div>

                            <div id="confirm_password_div" class="d-none">
                                <label for="confirm_password" class="form-label">Confirm New Password</label>
                                <input type="password" id="confirm_password" name="confirm_password" class="form-control" readonly>
                            </div>
                            <?php
                                if (isset($_SESSION['account_success'])) {
                                    echo '<div class="alert alert-success">' . $_SESSION['account_success'] . '</div>';
                                    unset($_SESSION['account_success']);
                                }
                                if (isset($_SESSION['account_error'])) {
                                    echo '<div class="alert alert-danger">' . $_SESSION['account_error'] . '</div>';
                                    unset($_SESSION['account_error']);
                                }
                                ?>



                            <div class="mt-auto d-flex flex-column gap-2">
                                <a href="archive.php" class="btn btn-warning w-100 mb-2">Check Archive</a>
                                <div class="d-flex gap-3  justify-content-center" id="account-btn-group">
                                    <button type="button" id="edit-account-button" class="btn btn-primary w-50" onclick="toggleEditAccount()">Edit Account</button>
                                    <button type="button" id="cancel-account-button" class="btn btn-secondary w-50 d-none" onclick="toggleEditAccount()">Cancel</button>
                                    <button type="submit" form="account-settings-form" id="save-account-button" class="btn btn-success w-50 d-none">Save Account</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>


                <div class="card" style="flex: 1 1 calc(50% - 0.75rem); height: 100%; display: flex; flex-direction: column;">

                    <div class="px-4 py-3 border-bottom bg-light text-center">
                        <h3 class="mb-0 fw-semibold">Pomodoro Settings</h3>
                    </div>


                    <div class="card-body d-flex flex-column justify-content-between" style="flex: 1;">

                        <form id="pomodoro-settings-form" action="../taskflow/component functions/update_settings.php" method="POST" class="d-flex flex-column gap-4 flex-grow-1">
                            <div>
                                <label for="pomodoro_time" class="form-label">Pomodoro Time (minutes)</label>
                                <input type="number" id="pomodoro_time" name="pomodoro_time" class="form-control" value="<?= htmlspecialchars($user['pomodoro_time']) ?>" readonly required>
                            </div>

                            <div>
                                <label for="short_break" class="form-label">Short Break (minutes)</label>
                                <input type="number" id="shortbreak_time" name="shortbreak_time" class="form-control" value="<?= htmlspecialchars($user['shortbreak_time']) ?>" readonly required>
                            </div>

                            <div>
                                <label for="long_break" class="form-label">Long Break (minutes)</label>
                                <input type="number" id="longbreak_time" name="longbreak_time" class="form-control" value="<?= htmlspecialchars($user['longbreak_time']) ?>" readonly required>
                            </div>

                            <div>
                                <label for="pomodoro_goal" class="form-label">Pomodoro Goal</label>
                                <input type="number" id="pomodoro_goal" name="pomodoro_goal" class="form-control" value="<?= htmlspecialchars($user['pomodoro_goal']) ?>" readonly required>
                            </div>

                            <?php
                                if (isset($_SESSION['pomodoro_success'])) {
                                    echo '<div class="alert alert-success mt-auto d-flex justify-content-center gap-3">' . $_SESSION['pomodoro_success'] . '</div>';
                                    unset($_SESSION['pomodoro_success']);
                                }
                                if (isset($_SESSION['pomodoro_error'])) {
                                    echo '<div class="alert alert-danger mt-auto d-flex justify-content-center gap-3">' . $_SESSION['pomodoro_error'] . '</div>';
                                    unset($_SESSION['pomodoro_error']);
                                }
                                ?>


                            <div class="mt-auto d-flex justify-content-center gap-3">
                                <button type="button" id="edit-pomodoro-button" class="btn btn-primary w-50" onclick="toggleEditPomodoro()">Edit Pomodoro Settings</button>
                                <button type="submit" form="pomodoro-settings-form" id="save-pomodoro-button" class="btn btn-success w-50 d-none">Save Pomodoro Settings</button>
                            </div>
                        </form>
                    </div>
                </div>

            </div>
        </div>

    </div>
</div>


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
<script src="../assets/javascript/settings.js"></script>
</body>
</html>

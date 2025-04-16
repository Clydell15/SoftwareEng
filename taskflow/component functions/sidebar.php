<?php
$current_page = basename($_SERVER['PHP_SELF']);
$username = $_SESSION['username'] ?? 'Guest';
?>

<div class="sidebar d-flex flex-column p-3">
    <h3 class="text-center">TaskFlow</h3>

    <!-- Dashboard Section -->
    <div class="submenu">
        <a href="../taskflow/dashboard.php" class="nav-link <?php echo $current_page == 'dashboard.php' ? 'active' : ''; ?>">🗂️ Dashboard</a>
        <a href="../taskflow/to-do.php" class="nav-link <?php echo $current_page == 'to-do.php' ? 'active' : ''; ?>">📋 To-Do</a>
        <a href="../taskflow/completed.php" class="nav-link <?php echo $current_page == 'completed.php' ? 'active' : ''; ?>">✅ Completed Tasks</a>
    </div>

    <!-- Categories Section -->
    <span class="nav-title">Manage</span>
    <div class="submenu">
        <a href="../taskflow/categories.php" class="nav-link <?php echo $current_page == 'categories.php' ? 'active' : ''; ?>">🏷️ Categories</a>
    </div>

    <!-- Settings Section -->
    <div class="submenu">
        <a href="../taskflow/settings.php" class="nav-link <?php echo $current_page == 'settings.php' ? 'active' : ''; ?>">⚙️ Settings</a>
    </div>

    <div id="pomodoro-widget" class="mt-4 p-2 border rounded bg-light shadow-sm">

        <div class="d-flex justify-content-between mt-2">
            <button id="btn-pomodoro" class="btn btn-sm btn-outline-primary session-btn w-33 me-1" onclick="setPomodoro()">Pomodoro</button>
            <button id="btn-shortBreak" class="btn btn-sm btn-outline-success session-btn w-33 me-1" onclick="setShortBreak()">Short</button>
            <button id="btn-longBreak" class="btn btn-sm btn-outline-warning session-btn w-33 session-btn" onclick="setLongBreak()">Long</button>
        </div>
        <h4 id="timer-display" class="text-center mb-2">00:00</h4>
        <div class="d-flex justify-content-between mt-2">
            <button id="start-btn" class="btn btn-sm btn-success w-50 me-1" onclick="togglePomodoro()">Start</button>
            <button id="reset-btn" class="btn btn-sm btn-secondary w-50 ms-1" onclick="resetPomodoro()">Reset</button>
        </div>
        <button class="btn btn-sm btn-warning w-100 mt-2" onclick="showNotification('This is a test notification!')">🔔 Test Notification</button>

    </div>



    <!-- Bottom Section -->
    <div class="mt-auto">
        <button id="toggleThemeBtn" class="btn btn-outline-dark w-100">
            <span id="themeIcon">🌙</span> 
        </button>
        <a href="../authentication/logout.php" class="btn btn-danger w-100 mt-2">
            <i class="bi bi-box-arrow-right"></i> Logout
        </a>
    </div>
</div>



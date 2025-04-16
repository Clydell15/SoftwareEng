let timerInterval;
let currentSession = localStorage.getItem("currentSession") || 'pomodoro';

// Get session duration based on user settings
function getDurationForCurrentSession() {
    switch (currentSession) {
        case 'pomodoro': return userPomodoroSettings.pomodoro * 60;
        case 'shortBreak': return userPomodoroSettings.shortBreak * 60;
        case 'longBreak': return userPomodoroSettings.longBreak * 60;
        default: return userPomodoroSettings.pomodoro * 60;
    }
}

// Toggle Start/Pause
function togglePomodoro() {
    const stored = JSON.parse(localStorage.getItem("pomodoroData"));
    if (!stored || !stored.running) {
        startPomodoro();
    } else {
        pausePomodoro();
    }
}

// Start Pomodoro Timer
function startPomodoro() {
    const stored = JSON.parse(localStorage.getItem("pomodoroData"));
    const duration = getDurationForCurrentSession();
    const remaining = stored && stored.remaining ? stored.remaining : duration;
    const endTime = Date.now() + remaining * 1000;

    localStorage.setItem("pomodoroData", JSON.stringify({
        endTime,
        running: true,
        session: currentSession
    }));

    updateButtonState(true);
    runTimer();
}

// Pause Pomodoro Timer
function pausePomodoro() {
    clearInterval(timerInterval);
    const data = JSON.parse(localStorage.getItem("pomodoroData"));
    const remaining = Math.max(0, Math.floor((data.endTime - Date.now()) / 1000));

    localStorage.setItem("pomodoroData", JSON.stringify({
        remaining,
        running: false,
        session: currentSession
    }));

    updateButtonState(false);
}

// Reset Timer
function resetPomodoro() {
    clearInterval(timerInterval);
    localStorage.removeItem("pomodoroData");
    updateDisplay(getDurationForCurrentSession());
    updateButtonState(false);
}

// Set current session & update button styles
function setPomodoro() {
    currentSession = 'pomodoro';
    localStorage.setItem("currentSession", currentSession);
    resetPomodoro();
    updateDisplay(userPomodoroSettings.pomodoro * 60);
    highlightSession();
}

function setShortBreak() {
    currentSession = 'shortBreak';
    localStorage.setItem("currentSession", currentSession);
    resetPomodoro();
    updateDisplay(userPomodoroSettings.shortBreak * 60);
    highlightSession();
}

function setLongBreak() {
    currentSession = 'longBreak';
    localStorage.setItem("currentSession", currentSession);
    resetPomodoro();
    updateDisplay(userPomodoroSettings.longBreak * 60);
    highlightSession();
}

// Highlight active session button
function highlightSession() {
    const buttons = document.querySelectorAll(".session-btn");
    buttons.forEach(btn => {
        btn.classList.remove("active");
    });

    const activeBtn = document.getElementById(`btn-${currentSession}`);
    if (activeBtn) activeBtn.classList.add("active");
}

// Timer logic
function runTimer() {
    clearInterval(timerInterval);
    timerInterval = setInterval(() => {
        const data = JSON.parse(localStorage.getItem("pomodoroData"));
        if (!data || !data.endTime) return;

        const remaining = Math.max(0, Math.floor((data.endTime - Date.now()) / 1000));
        updateDisplay(remaining);

        if (remaining <= 0) {
            clearInterval(timerInterval);
            localStorage.removeItem("pomodoroData");
            updateButtonState(false);
            showNotification("Session complete!");
        
            // 🔁 Send session update to backend
            fetch("dashboard.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: new URLSearchParams({ session_type: currentSession })
            }).then(res => {
                if (res.ok) {
                    console.log("Session updated.");
                    window.location.reload();  // Reload the page after a successful session update
                } else {
                    console.error("Failed to update session.");
                }
            });
        }
    }, 1000);
}

// Format time
function updateDisplay(seconds) {
    const minutes = Math.floor(seconds / 60);
    const secs = seconds % 60;
    const formattedTime = `${String(minutes).padStart(2, '0')}:${String(secs).padStart(2, '0')}`;
    document.getElementById("timer-display").textContent = formattedTime;
}

// Start/Pause button
function updateButtonState(running) {
    const startBtn = document.getElementById("start-btn");
    if (running) {
        startBtn.textContent = 'Pause';
        startBtn.classList.remove("btn-success");
        startBtn.classList.add("btn-danger");
    } else {
        startBtn.textContent = 'Start';
        startBtn.classList.remove("btn-danger");
        startBtn.classList.add("btn-success");
    }
}

// Desktop Notification
function showNotification(message) {
    if (Notification.permission === "granted") {
        const notification = new Notification("TaskFlow Pomodoro Timer", {
            body: message,
            icon: "https://cdn-icons-png.flaticon.com/512/833/833472.png"
        });

        notification.onclick = function () {
            window.focus();
            window.location.href = "http://localhost/SoftwareEng/taskflow/dashboard.php"; 
        };
    } else if (Notification.permission !== "denied") {
        Notification.requestPermission().then(permission => {
            if (permission === "granted") {
                const notification = new Notification("TaskFlow Pomodoro Timer", {
                    body: message,
                    icon: "https://cdn-icons-png.flaticon.com/512/833/833472.png"
                });

                notification.onclick = function () {
                    window.focus();
                    window.location.href = "http://localhost/SoftwareEng/taskflow/dashboard.php"; 
                };
            }
        });
    }
}

// On load
window.addEventListener("load", () => {
    if (Notification.permission !== "granted" && Notification.permission !== "denied") {
        Notification.requestPermission();
    }

    // Restore session from storage
    currentSession = localStorage.getItem("currentSession") || 'pomodoro';
    highlightSession();

    const stored = JSON.parse(localStorage.getItem("pomodoroData"));
    if (stored) {
        currentSession = stored.session || 'pomodoro';
        highlightSession();

        if (stored.running) {
            runTimer();
            updateButtonState(true);
        } else if (stored.remaining) {
            updateDisplay(stored.remaining);
            updateButtonState(false);
        } else {
            updateDisplay(getDurationForCurrentSession());
            updateButtonState(false);
        }
    } else {
        updateDisplay(getDurationForCurrentSession());
        updateButtonState(false);
    }
});

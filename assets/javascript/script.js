document.addEventListener("DOMContentLoaded", () => {
    editFormSave();
    slider();
    initThemeToggle();
    initTaskManagement();
    initCategoryManagement();
    initForms();
    restoreScroll();
    manualAddTask();
});

window.addEventListener("load", () => setTimeout(restoreScroll, 50));
window.addEventListener("beforeunload", function () {
    const taskPane = document.querySelector(".task-view-pane");
    if (taskPane) {
        sessionStorage.setItem("scrollPosition", taskPane.scrollTop);
    }
});


async function editFormSave(){
    const editForm = document.getElementById("editForm");
    const editSubmitBtn = document.getElementById("editFormSubmitBtn");

    if (editForm && editSubmitBtn) {
        editSubmitBtn.addEventListener("click", async (event) => {
            console.log("🚀 Edit form submit button clicked directly...");
            await saveEditTask(editForm);
        });
        console.log("✅ Direct saveEditTask attached to editFormSubmitBtn");
    } else {
        console.error("❌ Missing editForm or editFormSubmitBtn!");
    }
}

function manualAddTask(){
    const manualToggle = document.getElementById("manualTaskToggle");
    const manualFields = document.getElementById("manualTaskFields");
    const manualDifficulty = document.getElementById("manual-difficulty");
    const manualDifficultyValue = document.getElementById("manual-difficulty-value");

    if (manualToggle && manualFields) {
        manualToggle.addEventListener("change", function () {
            if (this.checked) {
                manualFields.style.display = "";
                // Set default value for slider
                if (manualDifficulty && manualDifficultyValue) {
                    manualDifficultyValue.textContent = `Difficulty: ${parseFloat(manualDifficulty.value).toFixed(1)}`;
                    manualDifficulty.addEventListener("input", () => {
                        manualDifficultyValue.textContent = `Difficulty: ${parseFloat(manualDifficulty.value).toFixed(1)}`;
                    });
                }
                renderCurrentTags([], "manual-current-tags");
                populateTagDropdown([], "manual-tag-dropdown", "manual-current-tags");
            } else {
                manualFields.style.display = "none";
            }
        });
    }
}

function slider(){
    const slider = document.getElementById('edit-difficulty');
    const valueDisplay = document.getElementById('edit-difficulty-value');

    if (slider && valueDisplay) {
        // Initial display value
        valueDisplay.textContent = `Difficulty: ${parseFloat(slider.value).toFixed(1)}`;

        // Update when user slides
        slider.addEventListener('input', () => {
            const value = parseFloat(slider.value).toFixed(1);
            valueDisplay.textContent = `Difficulty: ${value}`;
        });
    }

}

const savedTheme = localStorage.getItem("theme") || "light";

/* ========================
   🌓 THEME TOGGLE
   ======================== */
function initThemeToggle() {
    const themeSelect = document.getElementById("themeSelect");
    const toggleThemeBtns = document.querySelectorAll("#toggleThemeBtn");
    const themeIcons = document.querySelectorAll("#themeIcon");
    const body = document.documentElement;

    const savedTheme = localStorage.getItem("theme") || "light";
    applyTheme(savedTheme);

    function applyTheme(theme) {
        body.setAttribute("data-bs-theme", theme);
        localStorage.setItem("theme", theme);
        if (themeSelect) themeSelect.value = theme;
        themeIcons.forEach(icon => (icon.textContent = theme === "dark" ? "☀️" : "🌙"));
    }

    themeSelect?.addEventListener("change", () => applyTheme(themeSelect.value));
    toggleThemeBtns.forEach(btn => btn.addEventListener("click", () => {
        applyTheme(body.getAttribute("data-bs-theme") === "light" ? "dark" : "light");
    }));
}

/* ========================
   📌 GENERIC FORM HANDLER
   ======================== */
   function initForms() {
    document.getElementById("taskForm")?.addEventListener("submit", handleFormSubmit);
    document.getElementById("categoryForm")?.addEventListener("submit", handleFormSubmit);
    document.getElementById("subtaskForm")?.addEventListener("submit", handleFormSubmit);
}

async function handleFormSubmit(event) {
    console.log("🚀 Submit button clicked!");
    event.preventDefault();
    console.log("Form submission started...");
    const form = event.target;
    const submitButton = form.querySelector("button[type='submit']");
    const spinner = submitButton.querySelector(".spinner-border");
    const hasSpinner = !!spinner;

    const originalText = submitButton.innerHTML;

    // Clear any existing error message
    const modal = form.closest(".modal");
    const modalBody = modal?.querySelector(".modal-body");
    const existingError = modalBody?.querySelector("#error-message");
    if (existingError) existingError.remove();

    // Get input values
    let taskTitle = form.querySelector('input[name="taskTitle"]')?.value || "";
    let dueDateInput = form.querySelector('input[name="dueDate"]')?.value || "";
    let dueDate = new Date(dueDateInput);
    let now = new Date();
    let parentTaskTitle = form.querySelector('input[name="parentTaskTitle"]')?.value || "";
    let subtaskTitle = form.querySelector('input[name="subtaskTitle"]')?.value || "";
    let parentTaskId = form.querySelector('input[name="parentTaskId"]')?.value || "";
    let aiGenerate = form.querySelector('input[name="ai_generate"]')?.value === "1";
    
    if (form.id === "taskForm" && dueDate < now) {
        console.log("Due date is in the past!");
        
        const modal = form.closest(".modal");
        const modalBody = modal?.querySelector(".modal-body");

        if (modalBody) {
            // Remove existing error if any
            const existingError = modalBody.querySelector("#error-message");
            if (existingError) existingError.remove();

            // Create and insert error message
            const errorElement = document.createElement("p");
            errorElement.id = "error-message";
            errorElement.className = "text-warning text-center";
            errorElement.textContent = "Due date must be in the future.";
            modalBody.appendChild(errorElement);
        } else {
            console.warn("⚠️ Could not find .modal-body to display error.");
        }

        // Restore submit button UI
        submitButton.innerHTML = originalText;
        spinner.classList.add("d-none");
        submitButton.disabled = false;

        return; // Cancel the rest of the submission
    }




    if (hasSpinner) {
        submitButton.style.width = submitButton.offsetWidth + "px";
        submitButton.innerHTML = `<span class="spinner-border spinner-border-sm"></span>`;
    }
    submitButton.disabled = true;


    console.log("Local AI Generate:", aiGenerate);
    console.log("Form AI Generate Value:", form.querySelector('input[name="ai_generate"]')?.value);

    try {
        let formData = new URLSearchParams();
        let aiData = null;

        // **CASE 1: TASK FORM (Creating a Parent Task)**
        if (form.id === "taskForm") {
            const manualToggle = document.getElementById("manualTaskToggle");
            const isManual = manualToggle && manualToggle.checked;

            if (isManual) {
                // Manual Add: Gather manual fields
                const difficulty = form.querySelector('input[name="difficulty_numeric"]')?.value || 5;
                const tags = getCurrentTags("manual-current-tags");
                const dueDate = form.querySelector('input[name="dueDate"]').value;

                // Send directly to add.php
                const addData = await addTaskToDatabase(
                    taskTitle,
                    difficulty,
                    tags,
                    dueDate
                );

                if (addData && addData.success) {
                    form.reset();
                    closeModal(form);
                    addTaskToUI(addData.task);
                } else {
                    const errorMessage = addData && addData.message ? addData.message : "Unknown error occurred";
                    throw new Error("Error adding task: " + errorMessage);
                }
            } else {
                // AI Add: Use your existing AI logic
                formData.append("taskTitle", taskTitle);

                console.log("🔹 Data sent to API (Task):", Object.fromEntries(formData));

                // Send request to api.php
                const aiResponse = await fetch("../taskflow/component functions/api.php", {
                    method: "POST",
                    body: formData
                }); 

                const aiResponseText = await aiResponse.text();
                console.log("Raw AI Response:", aiResponseText);

                aiData = JSON.parse(aiResponseText);
                console.log("AI Response for Task:", aiData);

                if (!aiData.success) throw new Error(aiData.message || "AI task creation failed.");

                // Append dueDate after receiving AI response
                let dueDate = form.querySelector('input[name="dueDate"]').value;

                const addData = await addTaskToDatabase(
                    aiData.taskTitle,
                    aiData.difficulty_numeric,
                    Array.isArray(aiData.tags) ? aiData.tags.join(", ") : "",
                    dueDate
                );

                console.log("Task Data:", addData);

                if (addData && addData.success) {
                    form.reset();
                    closeModal(form);
                    addTaskToUI(addData.task);
                } else {
                    const errorMessage = addData && addData.message ? addData.message : "Unknown error occurred";
                    throw new Error("Error adding task: " + errorMessage);
                }
            }
        }
        

        // **CASE 2: SUBTASK FORM (AI ON)**
        else if (form.id === "subtaskForm" && aiGenerate) {
            formData.append("taskTitle", parentTaskTitle);
            formData.append("numSubtasks", subtaskTitle);
            formData.append("aiGenerate", "true");
            formData.append("parentTaskId", parentTaskId);

            console.log("🔹 Data sent to API (Subtask AI ON):", Object.fromEntries(formData));

            const aiResponse = await fetch("../taskflow/component functions/api.php", {
                method: "POST",
                body: formData
            });

            const aiResponseText = await aiResponse.text();
            console.log("Raw AI Response:", aiResponseText);

            aiData = JSON.parse(aiResponseText);
            console.log("AI Response for Subtasks (AI ON):", aiData);

            if (!aiData.success || !Array.isArray(aiData.subtasks)) throw new Error("AI subtask generation failed.");

            for (const subtask of aiData.subtasks) {
                const addData = await addSubtaskToDatabase(
                    parentTaskId,
                    subtask.title,
                    subtask.difficulty_numeric,
                    Array.isArray(subtask.tags) ? subtask.tags.join(", ") : ""
                );

                console.log("Subtask Data:", addData);

                if (!addData.success) throw new Error("Error adding subtask: " + addData.message);
            }

            form.reset();
            closeModal(form);
            refreshTaskList();
        }

        // **CASE 3: SUBTASK FORM (AI OFF)**
        else if (form.id === "subtaskForm" && !aiGenerate) {
            if (!subtaskTitle) throw new Error("Subtask title is required.");

            formData.append("subtaskTitle", subtaskTitle);
            formData.append("parentTaskId", parentTaskId);
            formData.append("aiGenerate", "false");

            console.log("🔹 Data sent to API (Subtask AI OFF):", Object.fromEntries(formData));

            const aiResponse = await fetch("../taskflow/component functions/api.php", {
                method: "POST",
                body: formData
            });

            const aiResponseText = await aiResponse.text();
            console.log("Raw AI Response:", aiResponseText);

            aiData = JSON.parse(aiResponseText);
            console.log("AI Response for Subtask (AI OFF):", aiData);

            if (!aiData.success || !Array.isArray(aiData.subtasks)) throw new Error("AI difficulty & tagging failed.");

            const addData = await addSubtaskToDatabase(
                parentTaskId,
                aiData.subtasks[0].title,
                aiData.subtasks[0].difficulty_numeric,
                Array.isArray(aiData.subtasks[0].tags) ? aiData.subtasks[0].tags.join(", ") : ""
            );

            console.log("Subtask Data:", addData);

            if (addData && addData.success) {
                form.reset();
                closeModal(form);
                addTaskToUI(addData.subtask); 
            } else {
                const errorMessage = addData && addData.message ? addData.message : "Unknown error occurred";
                throw new Error("Error adding task: " + errorMessage);
            }
        }

        // **CASE 4: CATEGORY FORM**
        else if (form.id === "categoryForm") {
            let categoryName = form.querySelector('input[name="categoryName"]')?.value || "";

            if (!categoryName) throw new Error("Category title is required.");

            formData.append("categoryName", categoryName);

            console.log("🔹 Data sent to API (Category):", Object.fromEntries(formData));

            const categoryResponse = await fetch("../taskflow/component functions/add.php", {
                method: "POST",
                body: formData
            });

            const categoryResponseText = await categoryResponse.text();
            console.log("Raw Category Response:", categoryResponseText);

            let categoryData = JSON.parse(categoryResponseText);
            console.log("Category Response:", categoryData);

            if (categoryData.success) {
                form.reset();
                closeModal(form);
                refreshTaskList();
            } else {
        
                form.reset();
                closeModal(form);
                throw new Error("Error adding category: " + categoryData.message);
            }
        }


    } catch (error) {
        console.error("Form submission error:", error);
        alert(error.message);
    } finally {
        submitButton.innerHTML = originalText;
        if (hasSpinner) spinner.classList.add("d-none");
        submitButton.disabled = false;
    }
}

async function saveEditTask(form) {
    console.log("🚀 Saving edited task...");

    const idInput = form.querySelector('input[name="id"]');
    const typeInput = form.querySelector('input[name="type"]');
    const titleInput = form.querySelector('input[name="title"]');
    const difficultyInput = form.querySelector('input[name="difficulty"]');
    console.log("🔍 id:", idInput);
    console.log("🔍 type:", typeInput);
    console.log("🔍 title:", titleInput);
    console.log("🔍 difficulty:", difficultyInput);


    if (!idInput || !typeInput || !titleInput || !difficultyInput) {
        console.error("❌ One or more input fields are missing in the edit form.");
        return;
    }

    const id = idInput.value;
    const type = typeInput.value;
    const title = titleInput.value.trim();
    const difficulty = parseFloat(difficultyInput.value) || 0;
    const tags = getCurrentTags(); // Make sure this exists and returns array or string
    console.log("🔍 id:", id);
    console.log("🔍 type:", type);
    console.log("🔍 title:", title);
    console.log("🔍 difficulty:", difficulty);
    console.log("🔍 tags:", tags);


    if (!title) {
        console.error("⚠️ Task title is required.");
        return;
    }

    try {
        const response = await fetch("../taskflow/component functions/edit_task.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ id, type, title, difficulty, tags })
        });

        const rawText = await response.text();
        console.log("🔹 Raw Edit Task Response:", rawText);
        
        const data = JSON.parse(rawText);

        if (data.success) {
            console.log("✅ Task updated successfully:", data.task);
            // Handle success: reset form, close modal, refresh list
            form.reset();
            closeModal(form);
            refreshTaskList();
        } else {
            throw new Error(data.message || "Failed to save task changes.");
        }
    } catch (error) {
        console.error("❌ Error saving task:", error.message);
    }
}

/**
 * Close modal after form submission.
 */
function closeModal(form) {
    const modal = form.closest(".modal");
    const modalInstance = modal ? bootstrap.Modal.getInstance(modal) : null;
    modalInstance?.hide();
}

/**
 * Sends a new task to the database.
 */
async function addTaskToDatabase(title, difficulty, tags, dueDate) {  
    const formData = new FormData();
    formData.append("taskTitle", title);
    formData.append("difficulty_numeric", difficulty);
    formData.append("tags", JSON.stringify(tags)); 


    let formattedDueDate = dueDate.replace('T', ' '); 

  
    if (formattedDueDate.length === 16) { 
        formattedDueDate += ":00";
    }

   
    formData.append("due_date", formattedDueDate);  

    console.log("Tags before sending:", tags); // Debugging line
    console.log("Due Date before sending:", formattedDueDate); // Debugging line

    const response = await fetch("../taskflow/component functions/add.php", {
        method: "POST",
        body: formData
    });

    const rawText = await response.text();
    console.log("Raw Response from PHP:", rawText); 

    try {
        return JSON.parse(rawText);
    } catch (error) {
        console.error("Error parsing JSON:", error);
        return { success: false, message: "Invalid JSON response" };
    }
}





/**
 * Sends a new subtask to the database.
 */
async function addSubtaskToDatabase(parentTaskId, title, difficulty, tags) {
    const formData = new FormData();
    formData.append("parentTaskId", parentTaskId);
    formData.append("subtaskTitle", title);
    formData.append("difficulty_numeric", difficulty);
    formData.append("tags", JSON.stringify(tags)); 

    const response = await fetch("../taskflow/component functions/add.php", {
        method: "POST",
        body: formData
    });

    const rawText = await response.text();
    console.log("Raw Response from PHP:", rawText);

    try {
        return JSON.parse(rawText);
    } catch (error) {
        console.error("Error parsing JSON:", error);
        return { success: false, message: "Invalid JSON response" };
    }
}







/* ========================
   ✅ ADD TASK AND CATEGORY TO UI
   ======================== */
   function createTaskElement(task) {
        // Create list item
        const taskElement = document.createElement("li");
        taskElement.className = "list-group-item task d-flex flex-column";
        taskElement.draggable = true;
        taskElement.dataset.taskId = task.id;
        taskElement.style.marginBottom = "5px";

        // ✅ Task Content (Checkbox + Title)
        taskElement.innerHTML = `
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <input type="checkbox" class="task-checkbox" data-task-id="${task.id}" ${task.status === "completed" ? "checked" : ""}>
                    <strong>${sanitize(task.title)}</strong>
                </div>
                <div class="d-flex flex-wrap">
                    ${task.tags ? task.tags.map(tag => `<span class="badge bg-info text-dark me-2">${sanitize(tag)}</span>`).join("") : ""}
                </div>
            </div>
            ${task.subtasks && task.subtasks.length > 0 ? generateSubtasksHTML(task.subtasks) : ""}
        `;

        return taskElement;
    }

// ✅ Add Task to UI
function addTaskToUI(task) {
    document.getElementById("task-list")?.prepend(createTaskElement(task));
    refreshTaskList();
}

// ✅ Add Category to UI
function addCategoryToUI(category) {
    const categoryList = document.getElementById("category-list");
    if (!categoryList) return;

    const categoryElement = document.createElement("li");
    categoryElement.className = "list-group-item d-flex justify-content-between align-items-center";
    categoryElement.dataset.categoryId = category.id;
    categoryElement.innerHTML = `
        <span>${category.name}</span>
        <span class="badge bg-primary">${category.task_count} tasks</span>
    `;

    categoryList.prepend(categoryElement);
    refreshTaskList();
}

// ✅ Helper Functions
function sanitize(text) {
    const div = document.createElement("div");
    div.textContent = text;
    return div.innerHTML;
}

function generateSubtasksHTML(subtasks) {
    return `
        <ul class="list-group mt-2">
            ${subtasks.map(subtask => `
                <li class="list-group-item d-flex align-items-center">
                    <input type="checkbox" class="subtask-checkbox me-2" data-subtask-id="${subtask.id}" ${subtask.status === "completed" ? "checked" : ""}>
                    ${sanitize(subtask.title)}
                </li>
            `).join("")}
        </ul>
    `;
}




/* ========================
   ✅ TASK MANAGEMENT (Drag & Drop + Status Update)
   ======================== */
function initTaskManagement() {
    const taskList = document.getElementById("task-list");
    if (!taskList) return;

    let draggedTask = null;

    // ✅ Task Checkbox Handling (Event Delegation)
    taskList.addEventListener("change", event => {
        if (event.target.matches(".task-checkbox, .subtask-checkbox")) {
            const taskId = event.target.dataset.taskId || event.target.dataset.subtaskId;
            if (taskId) updateTaskStatus(taskId, event.target.checked ? "completed" : "pending");
        }
    });

    // ✅ Drag & Drop Handling
    taskList.addEventListener("dragstart", event => {
        if (event.target.classList.contains("task")) {
            draggedTask = event.target;
            draggedTask.classList.add("dragging");
        }
    });

    taskList.addEventListener("dragend", () => {
        if (draggedTask) {
            draggedTask.classList.remove("dragging");
            draggedTask = null;
            saveTaskOrder();
        }
    });

    taskList.addEventListener("dragover", event => {
        event.preventDefault();
        const afterElement = getDragAfterElement(taskList, event.clientY);
        const draggingTask = document.querySelector(".dragging");
        afterElement ? taskList.insertBefore(draggingTask, afterElement) : taskList.appendChild(draggingTask);
    });

    // ✅ Open Add Subtask Modal & Set Parent Task ID
    taskList.addEventListener("click", event => {
        if (event.target.classList.contains("add-subtask-btn")) {
            const parentTaskId = event.target.dataset.taskId;
            document.getElementById("parentTaskId").value = parentTaskId;
            document.getElementById("parentTaskTitle").value = event.target.closest(".task").querySelector("strong").textContent;

            const subtaskModalElement = document.getElementById("addSubTaskModal");
            const subtaskModal = new bootstrap.Modal(subtaskModalElement);
            subtaskModal.show();

            subtaskModalElement.addEventListener("shown.bs.modal", function () {
                console.log("Subtask modal opened. Initializing AI Toggle...");
                aiGenerateToggle();  
            }, { once: true });
        }
    });


    // ✅ Delete Task/Subtask (Event Delegation)
    taskList.addEventListener("click", event => {
        const button = event.target.closest("button"); 
        if (!button) return;

        if (button.classList.contains("delete-task-btn") || button.classList.contains("delete-subtask-btn")) {
            if (confirm("Are you sure you want to delete this task?")) {
                const taskId = button.dataset.taskId || button.dataset.subtaskId;
                if (!taskId) {
                    alert("Error: Task ID is missing!");
                    return;
                }

                console.log("Attempting to delete task with ID:", taskId);

                fetch('../taskflow/component functions/delete_task.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `taskId=${taskId}`
                })
                .then(response => response.text())
                .then(text => {
                    console.log("Raw response from server:", text);
                    try {
                        const data = JSON.parse(text);
                        console.log("Parsed JSON response:", data);
                        if (data.success) {
                            refreshTaskList();
                        } else {
                            alert("Error deleting task: " + (data.message || "Unknown error"));
                        }
                    } catch (error) {
                        console.error("Error parsing JSON:", error);
                        alert("Server response is not valid JSON. Check console for details.");
                    }
                })
                .catch(error => {
                    console.error("Fetch error:", error);
                    alert("Fetch request failed. Check console for details.");
                });
                
            }
        }
    });

    // ✅ Edit Task/Subtask Button Handling (Event Delegation)
    taskList.addEventListener("click", event => {
        const editBtn = event.target.closest(".edit-task-btn") || event.target.closest(".edit-subtask-btn");
        if (!editBtn) return;

        const isTask = editBtn.classList.contains("edit-task-btn");
        const id = isTask ? editBtn.dataset.taskId : editBtn.dataset.subtaskId;
        const type = isTask ? "task" : "subtask";

        let item;
        if (isTask) {
            item = document.querySelector(`[data-task-id="${id}"]`);
        } else {
            // For subtask, find the checkbox first and get closest .subtask element
            const checkbox = document.querySelector(`.subtask-checkbox[data-subtask-id="${id}"]`);
            if (!checkbox) return;
            item = checkbox.closest(".subtask");
        }
        if (!item) return;

        const title = isTask 
            ? item.querySelector("strong")?.innerText.trim() || ""
            : item.querySelector(".subtask-name")?.innerText.trim() || "";

        const difficulty = item.querySelector(".difficulty-label")?.dataset.difficulty || 5;

        // Fetch due date (assumes you render it in a span or data attribute)
        let dueDate = "";
        const dueDateSpan = item.querySelector(".difficulty-label");
        if (dueDateSpan && dueDateSpan.textContent.includes("Due:")) {
            // Example: "... | Due: Jun 22, 2025 5:00 PM"
            const match = dueDateSpan.textContent.match(/Due:\s*([A-Za-z0-9, :AMP]+)/);
            if (match && match[1]) {
                // Parse to Date object
                const parsed = new Date(match[1]);
                if (!isNaN(parsed)) {
                    // Format to YYYY-MM-DDTHH:MM for datetime-local
                    const pad = n => n.toString().padStart(2, "0");
                    dueDate = `${parsed.getFullYear()}-${pad(parsed.getMonth() + 1)}-${pad(parsed.getDate())}T${pad(parsed.getHours())}:${pad(parsed.getMinutes())}`;
                }
            }
        }


        const tags = Array.from(item.querySelectorAll(".badge")).map(b => b.innerText.trim());

        document.getElementById("editTaskid").value = id;
        document.getElementById("edit-type").value = type;
        document.getElementById("edit-title").value = title;
        document.getElementById("edit-difficulty").value = difficulty;
        document.getElementById("edit-difficulty-value").innerText = difficulty;
        const dueDateInput = document.getElementById("edit-dueDate");
        if (dueDateInput && dueDate) {
            dueDateInput.value = dueDate;
        }
        renderCurrentTags(tags);
        populateTagDropdown(tags);

        const modal = new bootstrap.Modal(document.getElementById("editModal"));
        modal.show();
    });




    applyDifficultyColors();
}



function renderCurrentTags(tags = [], containerId = "current-tags") {
    const tagContainer = document.getElementById(containerId);
    if (!tagContainer) return;
    tagContainer.innerHTML = "";

    tags.forEach(tag => {
        const badge = document.createElement("span");
        badge.className = "badge bg-success text-light me-1";
        badge.textContent = tag;
        badge.style.cursor = "pointer";
        badge.addEventListener("click", () => {
            badge.remove();
            populateTagDropdown(getCurrentTags(containerId), containerId.replace("current-tags", "tag-dropdown"), containerId);
        });
        tagContainer.appendChild(badge);
    });
}

function getCurrentTags(containerId = "current-tags") {
    return Array.from(document.querySelectorAll(`#${containerId} .badge`)).map(b => b.innerText.trim());
}

function populateTagDropdown(currentTags = [], dropdownId = "tag-dropdown", containerId = "current-tags") {
    const dropdown = document.getElementById(dropdownId);
    if (!dropdown) return;
    dropdown.innerHTML = '<option value="">-- Select Tag --</option>';

    (window.availableCategories || []).forEach(cat => {
        if (!currentTags.includes(cat.name)) {
            const option = document.createElement("option");
            option.value = cat.name;
            option.textContent = cat.name;
            dropdown.appendChild(option);
        }
    });

    dropdown.onchange = function () {
        const selected = this.value;
        if (selected) {
            const newTag = document.createElement("span");
            newTag.className = "badge bg-success text-light me-1";
            newTag.textContent = selected;
            newTag.style.cursor = "pointer";
            newTag.addEventListener("click", () => {
                newTag.remove();
                populateTagDropdown(getCurrentTags(containerId), dropdownId, containerId);
            });
            document.getElementById(containerId).appendChild(newTag);
            this.value = "";
            populateTagDropdown(getCurrentTags(containerId), dropdownId, containerId);
        }
    };
}


// Function to Assign Dynamic Colors to Difficulty Labels
function applyDifficultyColors() {
    document.querySelectorAll(".difficulty-label").forEach(label => {
        const difficulty = parseFloat(label.getAttribute("data-difficulty"));
        if (!isNaN(difficulty)) {
            label.style.backgroundColor = getDifficultyColor(difficulty);
            label.style.color =  "#000";
            label.style.fontWeight = "bold";
            label.style.fontSize = "0.6rem";
            label.style.borderRadius = "5px";
            label.style.padding = "2px 10px";  
            label.style.alignItems = "center";
            
        }
    });
}

// Function to Compute Gradient Color (Green → Yellow → Red)
function getDifficultyColor(difficulty) {
    const isDarkMode = savedTheme === "dark";

    let red, green, blue;

    if (difficulty <= 4.0) {
        // Light Green to Light Yellow (0.1 → 4.0)
        let ratio = (difficulty - 0.1) / (4.0 - 0.1);
        red = Math.round(200 * ratio + 50);
        green = 230;
        blue = 150;
    } else if (difficulty <= 7.5) {
        // Light Yellow to Light Orange (4.1 → 7.5)
        let ratio = (difficulty - 4.1) / (7.5 - 4.1);
        red = 230;
        green = Math.round(200 * (1 - ratio) + 100);
        blue = 120;
    } else {
        // Light Orange to Light Red (7.6 → 10.0)
        let ratio = (difficulty - 7.6) / (10.0 - 7.6);
        red = 240;
        green = Math.round(150 * (1 - ratio) + 50);
        blue = 100;
    }

    // **Dark Mode Adjustment**: Reduce brightness & add gray
    if (isDarkMode) {
        red = Math.round(red * 0.8 + 30);   
        green = Math.round(green * 0.8 + 30);
        blue = Math.round(blue * 0.8 + 30);
    }

    return `rgb(${red}, ${green}, ${blue})`;
}






/* ========================
   ✅ UPDATE TASK STATUS (AJAX)
   ======================== */
async function updateTaskStatus(taskId, status) {
    try {
        const response = await fetch("../taskflow/component functions/update_status.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ task_id: taskId, status })
        });

        const data = await response.json();
        console.log(data);
        refreshTaskList(); 
    } catch (error) {
        console.error("Error updating task:", error);
    }
}


function refreshTaskList() {
    const taskPane = document.querySelector(".task-view-pane");
    if (taskPane) {
        sessionStorage.setItem("scrollPosition", taskPane.scrollTop);
    }

    location.reload();
}


function restoreScroll() {
    const taskPane = document.querySelector(".task-view-pane");
    const savedScrollPosition = sessionStorage.getItem("scrollPosition");

    if (taskPane && savedScrollPosition !== null) {
        taskPane.scrollTop = parseInt(savedScrollPosition, 10);
        sessionStorage.removeItem("scrollPosition"); 
    }
}



/* ========================
   💾 SAVE TASK ORDER TO DATABASE
   ======================== */
async function saveTaskOrder() {
    const tasks = [...document.querySelectorAll("#task-list > .task")].map((task, index) => ({
        task_id: task.dataset.taskId,
        position: index + 1
    }));

    try {
        const response = await fetch("../taskflow/component functions/update-task-order.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(tasks)
        });

        const data = await response.json();
        console.log(data);
    } catch (error) {
        console.error("Error updating task order:", error);
    }
}

/* ========================
   🏗️ HELPER FUNCTION: Get Element After Dragged Item
   ======================== */
function getDragAfterElement(container, y) {
    return [...container.querySelectorAll(".task:not(.dragging)")].reduce((closest, child) => {
        const box = child.getBoundingClientRect();
        const offset = y - box.top - box.height / 2;
        return offset < 0 && offset > closest.offset ? { offset, element: child } : closest;
    }, { offset: Number.NEGATIVE_INFINITY }).element;
}

function aiGenerateToggle() {
    console.log("aiGenerateToggle() function is running!");

    const aiToggle = document.getElementById("aiToggle");
    const subtaskLabel = document.getElementById("subtaskLabel");
    const subtaskTitle = document.getElementById("subtaskTitle");
    const aiGenerate = document.getElementById("aiGenerate");
    const submitButton = document.querySelector("#subtaskForm button[type='submit']");

    if (!aiToggle || !subtaskLabel || !subtaskTitle || !aiGenerate || !submitButton) return;

    aiToggle.addEventListener("change", function () {
        if (this.checked) {
            // AI mode: Change input to number of subtasks
            subtaskLabel.textContent = "How many subtasks to be generated?";
            subtaskTitle.type = "number";
            subtaskTitle.min = "1";
            subtaskTitle.value = "1";
            aiGenerate.value = "1"; 
            console.log(aiGenerate.value); // Debugging line
            submitButton.textContent = "Generate Subtasks";
        } else {
            // Manual mode: Change input back to text
            subtaskLabel.textContent = "Subtask Title";
            subtaskTitle.type = "text";
            subtaskTitle.value = "";
            aiGenerate.value = "0"; 
            console.log(aiGenerate.value); // Debugging line
            submitButton.textContent = "Add Subtask";
        }
    });
}

function initCategoryManagement() {
    const categoryList = document.getElementById("category-list");
    if (!categoryList) return;

    categoryList.addEventListener("click", event => {
        const button = event.target.closest("button");
        if (!button) return;

        if (button.classList.contains("delete-category-btn")) {
            const categoryId = button.dataset.categoryId;
            if (!categoryId) {
                alert("Error: Category ID is missing!");
                return;
            }

            if (confirm("Are you sure you want to delete this category?")) {
                console.log("Attempting to delete category with ID:", categoryId);

                fetch('../taskflow/component functions/delete_Category.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `categoryId=${categoryId}`
                })
                .then(response => response.text())
                .then(text => {
                    console.log("Raw response from server:", text);
                    try {
                        const data = JSON.parse(text);
                        if (data.success) {
                            
                            button.closest("li").remove();
                        } else {
                            alert("Error deleting category: " + (data.message || "Unknown error"));
                        }
                    } catch (error) {
                        console.error("Error parsing JSON:", error);
                        alert("Server response is not valid JSON. Check console.");
                    }
                })
                .catch(error => {
                    console.error("Fetch error:", error);
                    alert("Fetch request failed. Check console.");
                });
            }
        }
    });
}

// Function to update session counts
function updateSessionCount(type) {
    fetch('dashboard.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams({
            session_type: type
        })
    })
    .then(response => response.text())
    .then(data => {
        console.log(data);
        location.reload(); 
    })
    .catch(error => {
        console.error('Error:', error);
    });
}

// Function to reset all session counts
function resetSessionCounts() {
    fetch('dashboard.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams({
            reset: true
        })
    })
    .then(response => response.text())
    .then(data => {
        console.log(data);
        location.reload(); 
    })
    .catch(error => {
        console.error('Error:', error);
    });
}
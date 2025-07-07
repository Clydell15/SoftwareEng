
<!-- Task add ai/manual -->
<div class="modal fade" id="addTaskModal" data-bs-backdrop="static" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Task</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="taskForm" autocomplete="off">
                    <!-- Manual Add Toggle -->
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" id="manualTaskToggle">
                        <label class="form-check-label" for="manualTaskToggle">Manually add task</label>
                    </div>

                    <!-- Common: Task Title -->
                    <div class="mb-3">
                        <label for="taskTitle" class="form-label">Task Title</label>
                        <input type="text" id="taskTitle" name="taskTitle" class="form-control" required>
                    </div>

                    <!-- Manual Fields (hidden by default) -->
                    <div id="manualTaskFields" style="display: none;">
                        <div class="mb-3">
                            <label for="manual-difficulty" class="form-label">Difficulty</label>
                            <input type="range" class="form-range" min="1" max="10" step="0.1" id="manual-difficulty" name="difficulty_numeric">
                            <div id="manual-difficulty-value" class="text-end"></div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Current Categories</label>
                            <div id="manual-current-tags" class="d-flex flex-wrap gap-1"></div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Add Category</label>
                            <select id="manual-tag-dropdown" class="form-select">
                                <option value="">-- Select Category --</option>
                            </select>
                        </div>
                    </div>

                    <!-- Deadline (always shown) -->
                    <div class="mb-3">
                        <label for="dueDate" class="form-label">Due Date</label>
                        <input type="datetime-local" id="dueDate" name="dueDate" class="form-control" required>
                    </div>

                    <div class="d-flex justify-content-center">
                        <button type="submit" class="btn btn-success w-100">
                            Add Task
                            <span class="spinner-border spinner-border-sm ms-2 d-none" role="status"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>




<!-- Category -->
<div class="modal fade" id="addCategoryModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="categoryForm" autocomplete="off">
                    <div class="mb-3">
                        <label for="categoryName" class="form-label">Category Name</label>
                        <input type="text" id="categoryName" name="categoryName" class="form-control" required>
                    </div>
                    <div class="d-flex justify-content-center">
                        <button type="submit" class="btn btn-success w-100">
                            Add Category
                            <span class="spinner-border spinner-border-sm ms-2 d-none" role="status"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>


<!-- Subtask -->
<div class="modal fade" id="addSubTaskModal" data-bs-backdrop="static" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Subtask</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="subtaskForm" autocomplete="off">
                    <!-- Hidden Fields -->
                    <input type="hidden" id="parentTaskId" name="parentTaskId">
                    <input type="hidden" id="parentTaskTitle" name="parentTaskTitle">
                    <input type="hidden" id="aiGenerate" name="ai_generate" value="0">

                    <!-- Manual Add Toggle -->
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" id="manualSubtaskToggle">
                        <label class="form-check-label" for="manualSubtaskToggle">Manually add subtask</label>
                    </div>
                    <!-- AI Generate Toggle (hidden when manual is on) -->
                    <div class="form-check form-switch mb-3" id="aiToggleContainer">
                        <input class="form-check-input" type="checkbox" id="aiToggle">
                        <label class="form-check-label" for="aiToggle">AI Generate Subtasks</label>
                    </div>

                    <!-- Manual Fields (hidden by default) -->
                    <div id="manualSubtaskFields" style="display: none;">
                        <div class="mb-3">
                            <label for="manual-subtask-difficulty" class="form-label">Difficulty</label>
                            <input type="range" class="form-range" min="1" max="10" step="0.1" id="manual-subtask-difficulty" name="difficulty_numeric">
                            <div id="manual-subtask-difficulty-value" class="text-end"></div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Current Categories</label>
                            <div id="manual-subtask-current-tags" class="d-flex flex-wrap gap-1"></div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Add Category</label>
                            <select id="manual-subtask-tag-dropdown" class="form-select">
                                <option value="">-- Select Category --</option>
                            </select>
                        </div>
                    </div>

                    <!-- Subtask Title / Number of Subtasks -->
                    <div class="mb-3">
                        <label id="subtaskLabel" for="subtaskTitle" class="form-label">Subtask Title</label>
                        <input type="text" id="subtaskTitle" name="subtaskTitle" class="form-control" required>
                    </div>
                    <div class="d-flex justify-content-center">
                        <button type="submit" class="btn btn-success w-100">
                            Add Subtask
                            <span class="spinner-border spinner-border-sm ms-2 d-none" role="status"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Edit -->
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editModalLabel">Edit Task</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="editForm" autocomplete="off">
                    <input type="hidden" id="editTaskid" name="id">
                    <input type="hidden" id="edit-type" name="type">

                    <div class="mb-3">
                        <label for="edit-title" class="form-label">Title</label>
                        <input type="text" class="form-control" id="edit-title" name="title" required>
                    </div>

                    <div class="mb-3">
                        <label for="edit-difficulty" class="form-label">Difficulty</label>
                        <input type="range" class="form-range" min="1" max="10" step="0.1" id="edit-difficulty" name="difficulty">
                        <div id="edit-difficulty-value" class="text-end"></div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Current Categories</label>
                        <div id="current-tags" class="d-flex flex-wrap gap-1"></div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Add Category</label>
                        <select id="tag-dropdown" class="form-select">
                            <option value="">-- Select Category --</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="edit-dueDate" class="form-label">Due Date</label>
                        <input type="datetime-local" id="edit-dueDate" name="dueDate" class="form-control" required>
                    </div>

                    <div class="d-flex justify-content-center">
                        <button type="submit" id="editFormSubmitBtn" class="btn btn-success w-100">
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
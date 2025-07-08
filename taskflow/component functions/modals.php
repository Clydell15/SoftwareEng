
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

<!-- PDF Export Modal -->
<div class="modal fade" id="pdfExportModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">📄 Export to PDF</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="pdfExportForm">
                    <div class="mb-4">
                        <h6 class="fw-bold">Select Pages to Include:</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input page-checkbox" type="checkbox" id="export-todo" value="todo">
                                    <label class="form-check-label" for="export-todo">📋 To-Do Tasks</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input page-checkbox" type="checkbox" id="export-completed" value="completed">
                                    <label class="form-check-label" for="export-completed">✅ Completed Tasks</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input page-checkbox" type="checkbox" id="export-categories" value="categories">
                                    <label class="form-check-label" for="export-categories">🏷️ Categories</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input page-checkbox" type="checkbox" id="export-archive" value="archive">
                                    <label class="form-check-label" for="export-archive">🗃️ Archive</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Dynamic Content Selection -->
                    <div id="content-selection" style="display: none;">
                        <h6 class="fw-bold">Select Content to Include:</h6>
                        
                        <!-- To-Do Tasks Selection -->
                        <div id="todo-selection" class="content-section" style="display: none;">
                            <h6 class="text-primary">📋 To-Do Tasks</h6>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="todo-all" checked>
                                <label class="form-check-label" for="todo-all">All Tasks</label>
                            </div>
                            <div id="todo-tasks-list" class="ms-3 mt-2"></div>
                        </div>

                        <!-- Completed Tasks Selection -->
                        <div id="completed-selection" class="content-section" style="display: none;">
                            <h6 class="text-success">✅ Completed Tasks</h6>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="completed-all" checked>
                                <label class="form-check-label" for="completed-all">All Completed Tasks</label>
                            </div>
                            <div id="completed-tasks-list" class="ms-3 mt-2"></div>
                        </div>

                        <!-- Categories Selection -->
                        <div id="categories-selection" class="content-section" style="display: none;">
                            <h6 class="text-info">🏷️ Categories</h6>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="categories-all" checked>
                                <label class="form-check-label" for="categories-all">All Categories</label>
                            </div>
                            <div id="categories-list" class="ms-3 mt-2"></div>
                        </div>

                        <!-- Archive Selection -->
                        <div id="archive-selection" class="content-section" style="display: none;">
                            <h6 class="text-warning">🗃️ Archive</h6>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="archive-all" checked>
                                <label class="form-check-label" for="archive-all">All Archived Items</label>
                            </div>
                            <div id="archive-items-list" class="ms-3 mt-2"></div>
                        </div>
                    </div>

                    <!-- Export Options -->
                    <div class="mt-4">
                        <h6 class="fw-bold">Export Options:</h6>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="include-subtasks" checked>
                            <label class="form-check-label" for="include-subtasks">Include Subtasks</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="include-tags" checked>
                            <label class="form-check-label" for="include-tags">Include Tags/Categories</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="include-due-dates" checked>
                            <label class="form-check-label" for="include-due-dates">Include Due Dates</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="include-difficulty" checked>
                            <label class="form-check-label" for="include-difficulty">Include Difficulty Levels</label>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="generate-pdf-btn">
                    <i class="bi bi-file-earmark-pdf"></i> Generate PDF
                    <span class="spinner-border spinner-border-sm d-none" role="status"></span>
                </button>
            </div>
        </div>
    </div>
</div>
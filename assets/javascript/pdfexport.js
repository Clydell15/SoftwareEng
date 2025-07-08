// PDF Export Modal Logic
document.addEventListener('DOMContentLoaded', function() {
    const pageCheckboxes = document.querySelectorAll('.page-checkbox');
    const contentSelection = document.getElementById('content-selection');

    // Handle page selection changes
    pageCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            updateContentSelection();
            if (this.checked) {
                loadContentForPage(this.value);
            }
        });
    });

    function updateContentSelection() {
        const anyChecked = Array.from(pageCheckboxes).some(cb => cb.checked);
        if (contentSelection) {
            contentSelection.style.display = anyChecked ? 'block' : 'none';
        }

        // Show/hide content sections based on selected pages
        pageCheckboxes.forEach(checkbox => {
            const section = document.getElementById(`${checkbox.value}-selection`);
            if (section) {
                section.style.display = checkbox.checked ? 'block' : 'none';
            }
        });
    }

    function loadContentForPage(pageType) {
        // Fetch content via AJAX - properly encode the URL
        fetch(`../taskflow/component%20functions/fetch_export_data.php?type=${pageType}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                populateContentList(pageType, data);
            })
            .catch(error => {
                console.error('Error loading content:', error);
                // Show error message to user
                const listContainer = document.getElementById(`${pageType}-${pageType === 'categories' ? 'list' : 'tasks-list'}`);
                if (listContainer) {
                    listContainer.innerHTML = '<small class="text-danger">Error loading content</small>';
                }
            });
    }

    function populateContentList(pageType, data) {
        const listContainer = document.getElementById(`${pageType}-${pageType === 'categories' ? 'list' : 'tasks-list'}`);
        if (!listContainer) return;

        listContainer.innerHTML = '';

        if (!data || data.length === 0) {
            listContainer.innerHTML = '<small class="text-muted">No items available</small>';
            return;
        }

        data.forEach(item => {
            const div = document.createElement('div');
            div.className = 'form-check';
            div.innerHTML = `
                <input class="form-check-input item-checkbox" type="checkbox" 
                       id="${pageType}-item-${item.id}" value="${item.id}" checked>
                <label class="form-check-label" for="${pageType}-item-${item.id}">
                    ${item.title || item.name}
                    ${item.status ? `<span class="badge bg-secondary ms-1">${item.status}</span>` : ''}
                </label>
            `;
            listContainer.appendChild(div);
        });
    }

    // Handle "All" checkboxes
    document.addEventListener('change', function(e) {
        if (e.target.id && e.target.id.endsWith('-all')) {
            const pageType = e.target.id.replace('-all', '');
            const itemCheckboxes = document.querySelectorAll(`#${pageType}-selection .item-checkbox`);
            itemCheckboxes.forEach(cb => cb.checked = e.target.checked);
        }
    });

    // Generate PDF button
    const generateBtn = document.getElementById('generate-pdf-btn');
    if (generateBtn) {
        generateBtn.addEventListener('click', function() {
            const btn = this;
            const spinner = btn.querySelector('.spinner-border');
            
            btn.disabled = true;
            if (spinner) spinner.classList.remove('d-none');

            // Collect selected data
            const selectedPages = Array.from(pageCheckboxes)
                .filter(cb => cb.checked)
                .map(cb => cb.value);

            if (selectedPages.length === 0) {
                alert('Please select at least one page to export.');
                btn.disabled = false;
                if (spinner) spinner.classList.add('d-none');
                return;
            }

            const exportOptions = {
                pages: selectedPages,
                include_subtasks: document.getElementById('include-subtasks')?.checked || false,
                include_tags: document.getElementById('include-tags')?.checked || false,
                include_due_dates: document.getElementById('include-due-dates')?.checked || false,
                include_difficulty: document.getElementById('include-difficulty')?.checked || false,
                selected_items: {}
            };

            // Collect selected items for each page
            selectedPages.forEach(pageType => {
                const itemCheckboxes = document.querySelectorAll(`#${pageType}-selection .item-checkbox:checked`);
                exportOptions.selected_items[pageType] = Array.from(itemCheckboxes).map(cb => cb.value);
            });

            console.log("Export Options being sent:", exportOptions);
            console.log("Selected pages:", exportOptions.pages);
            console.log("Selected items:", exportOptions.selected_items);

            const pdfUrl = '../assets/vendor/generate_pdf.php';

            // Send to PDF generation endpoint
            fetch(pdfUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(exportOptions)
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                // Check if response is JSON (error) or PDF
                const contentType = response.headers.get('content-type');
                if (contentType && contentType.includes('application/json')) {
                    return response.json().then(data => {
                        throw new Error(data.error || 'PDF generation failed');
                    });
                }
                return response.blob();
            })
            .then(blob => {
                if (blob.size === 0) {
                    throw new Error('Empty PDF file received');
                }
                
                // Download the PDF
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = `TaskFlow_Export_${new Date().toISOString().split('T')[0]}.pdf`;
                document.body.appendChild(a);
                a.click();
                window.URL.revokeObjectURL(url);
                document.body.removeChild(a);

                // Close modal
                const modal = document.getElementById('pdfExportModal');
                if (modal) {
                    const modalInstance = bootstrap.Modal.getInstance(modal);
                    if (modalInstance) {
                        modalInstance.hide();
                    }
                }

            })
            .catch(error => {
                console.error('Error generating PDF:', error);
                alert('Error generating PDF: ' + error.message);
            })
            .finally(() => {
                btn.disabled = false;
                if (spinner) spinner.classList.add('d-none');
            });
        });
    }
});
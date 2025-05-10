/**
 * Database Import Functionality
 * Handles database import via AJAX
 */
document.addEventListener('DOMContentLoaded', function() {
    const importButton = document.getElementById('import-db');
    const startImportButton = document.getElementById('startImport');
    const importForm = document.getElementById('importDatabaseForm');
    const sqlFileInput = document.getElementById('sqlFile');
    const progressContainer = document.getElementById('importProgressContainer');
    const progressBar = document.getElementById('importProgressBar');
    const statusElement = document.getElementById('importStatus');

    // Open import modal
    if (importButton) {
        importButton.addEventListener('click', function(e) {
            e.preventDefault();
            const importModal = new bootstrap.Modal(document.getElementById('importDatabaseModal'));
            importModal.show();
        });
    }

    // Handle import process
    if (startImportButton && importForm) {
        startImportButton.addEventListener('click', function() {
            // Validate form
            if (!importForm.checkValidity()) {
                importForm.reportValidity();
                return;
            }

            // Check if file is selected
            if (!sqlFileInput || !sqlFileInput.files || sqlFileInput.files.length === 0) {
                showNotification('Please select a SQL file to import', 'error');
                return;
            }

            // Prepare form data
            const formData = new FormData();
            formData.append('sqlFile', sqlFileInput.files[0]);

            // Show progress container, hide form
            importForm.classList.add('d-none');
            progressContainer.classList.remove('d-none');
            
            // Disable import button
            startImportButton.disabled = true;
            startImportButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Importing...';
            
            // Update progress
            updateProgress(10, 'Starting import process...');

            // Send AJAX request
            const xhr = new XMLHttpRequest();
            xhr.open('POST', '../api/dashboard/import-database.php', true);

            xhr.upload.onprogress = function(e) {
                if (e.lengthComputable) {
                    const percentComplete = Math.round((e.loaded / e.total) * 50); // First 50% is upload
                    updateProgress(percentComplete, 'Uploading file...');
                }
            };

            xhr.onload = function() {
                if (xhr.status === 200) {
                    try {
                        const response = JSON.parse(xhr.responseText);
                        
                        if (response.success) {
                            updateProgress(100, 'Import completed successfully!');
                            showNotification(response.message, 'success');
                            
                            // Close modal after 2 seconds
                            setTimeout(function() {
                                const importModal = bootstrap.Modal.getInstance(document.getElementById('importDatabaseModal'));
                                if (importModal) {
                                    importModal.hide();
                                }
                                
                                // Reset form after modal is closed
                                setTimeout(resetImportForm, 500);
                            }, 2000);
                        } else {
                            updateProgress(0, 'Import failed: ' + response.message);
                            showNotification('Import failed: ' + response.message, 'error');
                            setTimeout(resetImportForm, 3000);
                        }
                    } catch (e) {
                        updateProgress(0, 'Error processing response');
                        showNotification('Error processing server response', 'error');
                        setTimeout(resetImportForm, 3000);
                    }
                } else {
                    updateProgress(0, 'Server error: ' + xhr.status);
                    showNotification('Server error: ' + xhr.status, 'error');
                    setTimeout(resetImportForm, 3000);
                }
            };

            xhr.onerror = function() {
                updateProgress(0, 'Connection error');
                showNotification('Connection error. Please try again.', 'error');
                setTimeout(resetImportForm, 3000);
            };

            // Fake processing after upload completes
            xhr.onloadend = function() {
                if (xhr.status === 200) {
                    // Simulate processing time (going from 50% to 80%)
                    let currentProgress = 50;
                    const interval = setInterval(function() {
                        currentProgress += 5;
                        if (currentProgress <= 80) {
                            updateProgress(currentProgress, 'Processing import...');
                        } else {
                            clearInterval(interval);
                        }
                    }, 500);
                }
            };

            xhr.send(formData);
        });
    }

    /**
     * Update progress bar and status message
     * @param {number} percent - Progress percentage
     * @param {string} message - Status message
     */
    function updateProgress(percent, message) {
        if (progressBar && statusElement) {
            progressBar.style.width = percent + '%';
            progressBar.setAttribute('aria-valuenow', percent);
            progressBar.textContent = percent + '%';
            statusElement.textContent = message;
            
            // Change color based on progress
            progressBar.classList.remove('bg-danger', 'bg-warning', 'bg-info', 'bg-success');
            
            if (percent === 0) {
                progressBar.classList.add('bg-danger');
            } else if (percent < 50) {
                progressBar.classList.add('bg-warning');
            } else if (percent < 100) {
                progressBar.classList.add('bg-info');
            } else {
                progressBar.classList.add('bg-success');
            }
        }
    }

    /**
     * Reset the import form to its initial state
     */
    function resetImportForm() {
        if (importForm && progressContainer && startImportButton) {
            importForm.reset();
            importForm.classList.remove('d-none');
            progressContainer.classList.add('d-none');
            startImportButton.disabled = false;
            startImportButton.innerHTML = 'Import Database';
            updateProgress(0, '');
        }
    }

    /**
     * Display a notification to the user
     * @param {string} message - The message to display
     * @param {string} type - The type of notification (success, error, warning, info)
     */
    function showNotification(message, type = 'info') {
        // Check if we have a notification container
        let notificationContainer = document.getElementById('notification-container');
        
        // Create it if it doesn't exist
        if (!notificationContainer) {
            notificationContainer = document.createElement('div');
            notificationContainer.id = 'notification-container';
            notificationContainer.style.position = 'fixed';
            notificationContainer.style.top = '20px';
            notificationContainer.style.right = '20px';
            notificationContainer.style.zIndex = '9999';
            document.body.appendChild(notificationContainer);
        }
        
        // Create the notification element
        const notification = document.createElement('div');
        notification.className = `alert alert-${type} alert-dismissible fade show`;
        notification.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;
        
        // Add to container
        notificationContainer.appendChild(notification);
        
        // Remove after 5 seconds
        setTimeout(function() {
            notification.classList.remove('show');
            setTimeout(function() {
                if (notification.parentNode) {
                    notification.parentNode.removeChild(notification);
                }
            }, 150);
        }, 5000);
    }
}); 
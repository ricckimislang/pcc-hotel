/**
 * Database Backup Functionality
 * Handles the database backup operation via AJAX
 */
document.addEventListener('DOMContentLoaded', function() {
    const backupButton = document.getElementById('backup-db');
    
    if (backupButton) {
        backupButton.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Show loading state
            const originalText = backupButton.innerHTML;
            backupButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
            backupButton.disabled = true;
            
            // Create a hidden iframe to handle the download
            // This approach allows downloading files from AJAX calls
            const downloadFrame = document.createElement('iframe');
            downloadFrame.style.display = 'none';
            document.body.appendChild(downloadFrame);
            
            // Set the iframe source to our backup API endpoint
            downloadFrame.src = '../api/dashboard/backup-database.php';
            
            // Reset button after download starts (after a short delay)
            setTimeout(function() {
                backupButton.innerHTML = originalText;
                backupButton.disabled = false;
                
                // Show success message
                showNotification('Database backup initiated. If the download doesn\'t start automatically, please check your browser settings.', 'success');
                
                // Remove the iframe after download is complete (5 seconds should be enough for most downloads to start)
                setTimeout(function() {
                    document.body.removeChild(downloadFrame);
                }, 5000);
            }, 2000);
        });
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

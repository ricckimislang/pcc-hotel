<!-- Database Import Modal -->
<div class="modal fade" id="importDatabaseModal" tabindex="-1" aria-labelledby="importDatabaseModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="importDatabaseModalLabel">Import Database</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="importDatabaseForm" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="sqlFile" class="form-label">Select SQL File</label>
                        <input type="file" class="form-control" id="sqlFile" name="sqlFile" accept=".sql" required>
                        <div class="form-text">Only .sql files are allowed. Max file size: 50MB.</div>
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="confirmBackup" required>
                            <label class="form-check-label" for="confirmBackup">
                                I have backed up the current database
                            </label>
                        </div>
                    </div>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle"></i> <strong>IMPORTANT:</strong> This operation will DELETE ALL CURRENT DATA in the database and truncate all tables before importing new data. 
                        Make sure you have a backup before proceeding as this action cannot be undone.
                    </div>
                </form>
                <div id="importProgressContainer" class="d-none">
                    <div class="progress mb-3">
                        <div id="importProgressBar" class="progress-bar" role="progressbar" style="width: 0%;" 
                             aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0%</div>
                    </div>
                    <div id="importStatus" class="text-center"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="startImport">Import Database</button>
            </div>
        </div>
    </div>
</div> 
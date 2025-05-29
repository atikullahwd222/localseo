<!-- Add Purpose Modal -->
<div class="modal fade" id="addPurposeModal" tabindex="-1" aria-labelledby="addPurposeModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addPurposeModalLabel">Add New Purpose</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addPurposeForm">
                    <div class="mb-3">
                        <label for="purpose_name" class="form-label">Purpose Name</label>
                        <input type="text" class="form-control" id="purpose_name" name="name" required>
                        <div class="invalid-feedback" id="purpose_name_error"></div>
                    </div>
                    <div class="mb-3">
                        <label for="purpose_description" class="form-label">Description</label>
                        <textarea class="form-control" id="purpose_description" name="description" rows="3"></textarea>
                        <div class="invalid-feedback" id="purpose_description_error"></div>
                    </div>
                    <div class="mb-3">
                        <label for="purpose_categories" class="form-label">Compatible Site Categories</label>
                        <select class="form-control select2" id="purpose_categories" name="category_ids[]" multiple>
                            <!-- Categories will be loaded dynamically -->
                        </select>
                        <div class="form-text">Select which site categories this purpose is compatible with</div>
                        <div class="invalid-feedback" id="purpose_categories_error"></div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="savePurposeBtn">
                    <span class="btn-text">Save</span>
                    <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Purpose Modal -->
<div class="modal fade" id="editPurposeModal" tabindex="-1" aria-labelledby="editPurposeModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editPurposeModalLabel">Edit Purpose</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editPurposeForm">
                    <input type="hidden" id="edit_purpose_id">
                    <div class="mb-3">
                        <label for="edit_purpose_name" class="form-label">Purpose Name</label>
                        <input type="text" class="form-control" id="edit_purpose_name" name="name" required>
                        <div class="invalid-feedback" id="edit_purpose_name_error"></div>
                    </div>
                    <div class="mb-3">
                        <label for="edit_purpose_description" class="form-label">Description</label>
                        <textarea class="form-control" id="edit_purpose_description" name="description" rows="3"></textarea>
                        <div class="invalid-feedback" id="edit_purpose_description_error"></div>
                    </div>
                    <div class="mb-3">
                        <label for="edit_purpose_categories" class="form-label">Compatible Site Categories</label>
                        <select class="form-control select2" id="edit_purpose_categories" name="category_ids[]" multiple>
                            <!-- Categories will be loaded dynamically -->
                        </select>
                        <div class="form-text">Select which site categories this purpose is compatible with</div>
                        <div class="invalid-feedback" id="edit_purpose_categories_error"></div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="updatePurposeBtn">
                    <span class="btn-text">Update</span>
                    <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Purpose Modal -->
<div class="modal fade" id="deletePurposeModal" tabindex="-1" aria-labelledby="deletePurposeModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deletePurposeModalLabel">Delete Purpose</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this purpose?</p>
                <p class="text-warning"><strong>This action cannot be undone.</strong></p>
                <input type="hidden" id="delete_purpose_id">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeletePurposeBtn">
                    <span class="btn-text">Delete</span>
                    <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                </button>
            </div>
        </div>
    </div>
</div> 
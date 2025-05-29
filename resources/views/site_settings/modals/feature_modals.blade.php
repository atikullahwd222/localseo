<!-- Add Feature Modal -->
<div class="modal fade" id="addFeatureModal" tabindex="-1" aria-labelledby="addFeatureModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addFeatureModalLabel">Add New Feature</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addFeatureForm">
                    <div class="mb-3">
                        <label for="feature_name" class="form-label">Feature Name</label>
                        <input type="text" class="form-control" id="feature_name" name="name" required>
                        <div class="invalid-feedback" id="feature_name_error"></div>
                    </div>
                    <div class="mb-3">
                        <label for="feature_description" class="form-label">Description</label>
                        <textarea class="form-control" id="feature_description" name="description" rows="3"></textarea>
                        <div class="invalid-feedback" id="feature_description_error"></div>
                    </div>
                    <div class="mb-3">
                        <label for="feature_points" class="form-label">Points (1-10)</label>
                        <input type="number" class="form-control" id="feature_points" name="points" min="1" max="10" value="1" required>
                        <div class="form-text">Assign points to this feature (higher points = more impact on site rating)</div>
                        <div class="invalid-feedback" id="feature_points_error"></div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="saveFeatureBtn">
                    <span class="btn-text">Save</span>
                    <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Feature Modal -->
<div class="modal fade" id="editFeatureModal" tabindex="-1" aria-labelledby="editFeatureModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editFeatureModalLabel">Edit Feature</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editFeatureForm">
                    <input type="hidden" id="edit_feature_id">
                    <div class="mb-3">
                        <label for="edit_feature_name" class="form-label">Feature Name</label>
                        <input type="text" class="form-control" id="edit_feature_name" name="name" required>
                        <div class="invalid-feedback" id="edit_feature_name_error"></div>
                    </div>
                    <div class="mb-3">
                        <label for="edit_feature_description" class="form-label">Description</label>
                        <textarea class="form-control" id="edit_feature_description" name="description" rows="3"></textarea>
                        <div class="invalid-feedback" id="edit_feature_description_error"></div>
                    </div>
                    <div class="mb-3">
                        <label for="edit_feature_points" class="form-label">Points (1-10)</label>
                        <input type="number" class="form-control" id="edit_feature_points" name="points" min="1" max="10" value="1" required>
                        <div class="form-text">Assign points to this feature (higher points = more impact on site rating)</div>
                        <div class="invalid-feedback" id="edit_feature_points_error"></div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="updateFeatureBtn">
                    <span class="btn-text">Update</span>
                    <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Feature Modal -->
<div class="modal fade" id="deleteFeatureModal" tabindex="-1" aria-labelledby="deleteFeatureModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteFeatureModalLabel">Delete Feature</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this feature?</p>
                <p class="text-warning"><strong>This action cannot be undone.</strong></p>
                <input type="hidden" id="delete_feature_id">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteFeatureBtn">
                    <span class="btn-text">Delete</span>
                    <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                </button>
            </div>
        </div>
    </div>
</div> 
<!-- Add Country Modal -->
<div class="modal fade" id="addCountryModal" tabindex="-1" aria-labelledby="addCountryModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addCountryModalLabel">Add New Country</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addCountryForm">
                    <div class="mb-3">
                        <label for="country_name" class="form-label">Country Name</label>
                        <input type="text" class="form-control" id="country_name" name="name" required>
                        <div class="invalid-feedback" id="country_name_error"></div>
                    </div>
                    <div class="mb-3">
                        <label for="country_description" class="form-label">Description</label>
                        <textarea class="form-control" id="country_description" name="description" rows="3"></textarea>
                        <div class="invalid-feedback" id="country_description_error"></div>
                    </div>
                    <div class="mb-3">
                        <label for="country_categories" class="form-label">Compatible Site Categories</label>
                        <select class="form-control select2" id="country_categories" name="category_ids[]" multiple>
                            <!-- Categories will be loaded dynamically -->
                        </select>
                        <div class="form-text">Select which site categories this country is compatible with</div>
                        <div class="invalid-feedback" id="country_categories_error"></div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="saveCountryBtn">
                    <span class="btn-text">Save</span>
                    <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Country Modal -->
<div class="modal fade" id="editCountryModal" tabindex="-1" aria-labelledby="editCountryModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editCountryModalLabel">Edit Country</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editCountryForm">
                    <input type="hidden" id="edit_country_id">
                    <div class="mb-3">
                        <label for="edit_country_name" class="form-label">Country Name</label>
                        <input type="text" class="form-control" id="edit_country_name" name="name" required>
                        <div class="invalid-feedback" id="edit_country_name_error"></div>
                    </div>
                    <div class="mb-3">
                        <label for="edit_country_description" class="form-label">Description</label>
                        <textarea class="form-control" id="edit_country_description" name="description" rows="3"></textarea>
                        <div class="invalid-feedback" id="edit_country_description_error"></div>
                    </div>
                    <div class="mb-3">
                        <label for="edit_country_categories" class="form-label">Compatible Site Categories</label>
                        <select class="form-control select2" id="edit_country_categories" name="category_ids[]" multiple>
                            <!-- Categories will be loaded dynamically -->
                        </select>
                        <div class="form-text">Select which site categories this country is compatible with</div>
                        <div class="invalid-feedback" id="edit_country_categories_error"></div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="updateCountryBtn">
                    <span class="btn-text">Update</span>
                    <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Country Modal -->
<div class="modal fade" id="deleteCountryModal" tabindex="-1" aria-labelledby="deleteCountryModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteCountryModalLabel">Delete Country</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this country?</p>
                <p class="text-warning"><strong>This action cannot be undone.</strong></p>
                <input type="hidden" id="delete_country_id">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteCountryBtn">
                    <span class="btn-text">Delete</span>
                    <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                </button>
            </div>
        </div>
    </div>
</div> 
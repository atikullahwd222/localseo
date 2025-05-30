/**
 * Site Form Handlers
 * 
 * This script handles form submissions for Add and Edit Site forms.
 */

$(document).ready(function() {
    console.log('Site Form Handlers JS loaded');
    
    // Setup CSRF token for all AJAX requests
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    
    // Add a global AJAX error handler for handling CSRF token mismatches
    $(document).ajaxError(function(event, jqXHR, settings, thrownError) {
        if (jqXHR.status === 419) { // Laravel CSRF token mismatch status code
            console.error('CSRF token mismatch. Refreshing token...');
            // Reload the page to get a fresh CSRF token
            window.location.reload();
        }
    });
    
    // Add Site form submission
    $('#saveSiteBtn').on('click', function() {
        console.log('Save Site button clicked');
        submitAddSiteForm();
    });
    
    // Edit Site form submission
    $('#editSiteForm').on('submit', function(e) {
        e.preventDefault();
        console.log('Edit form submitted');
        submitEditSiteForm();
    });
    
    // Direct button handler for the edit button in the modal
    $('.edit_btn').on('click', function() {
        console.log('Edit Site save button clicked');
        submitEditSiteForm();
    });
    
    // Edit button click handler - use event delegation for dynamically added elements
    $(document).on('click', '.edit-btn', function() {
        console.log('Edit button clicked with ID:', $(this).val());
        const id = $(this).val();
        if (!id) {
            console.error('No site ID found for edit button');
            return;
        }
        fetchSiteDetails(id);
    });
    
    // Delete button click handler - use event delegation for dynamically added elements
    $(document).on('click', '.delete-btn', function() {
        console.log('Delete button clicked with ID:', $(this).val());
        const id = $(this).val();
        if (!id) {
            console.error('No site ID found for delete button');
            return;
        }
        confirmDelete(id);
    });
    
    /**
     * Fetch site details for editing
     */
    function fetchSiteDetails(id) {
        console.log('Fetching site details for ID:', id);
        
        // Show loading state
        $('#EditModal .modal-content').addClass('loading');
        
        $.ajax({
            url: `/sites/edit/${id}`,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                console.log('Site details response:', response);
                
                if (response.status === 200) {
                    populateEditForm(response);
                    
                    // Show the modal
                    $('#EditModal').modal('show');
                } else {
                    // Show error message with details if available
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.message || 'Failed to fetch site details'
                    });
                }
                
                // Remove loading state
                $('#EditModal .modal-content').removeClass('loading');
            },
            error: function(xhr, status, error) {
                console.error('Error fetching site details:', error);
                console.error('Status:', status);
                console.error('Response:', xhr.responseText);
                
                // Try to parse response for more detailed error
                let errorMessage = 'Failed to fetch site details. Please try again.';
                try {
                    const response = JSON.parse(xhr.responseText);
                    if (response.message) {
                        errorMessage = response.message;
                    }
                } catch (e) {
                    console.error('Could not parse error response:', e);
                }
                
                // Show error message
                Swal.fire({
                    icon: 'error',
                    title: 'Error ' + xhr.status,
                    text: errorMessage,
                    footer: 'Please check the console for more details'
                });
                
                // Remove loading state
                $('#EditModal .modal-content').removeClass('loading');
            }
        });
    }
    
    /**
     * Populate the edit form with site details
     */
    function populateEditForm(response) {
        const site = response.site;
        
        // Populate basic fields
        $('#edit_id').val(site.id);
        $('#edit_url').val(site.url);
        $('#edit_complete_url').val(site.complete_url);
        $('#edit_da').val(site.da);
        $('#edit_description').val(site.description);
        $('#edit_video_link').val(site.video_link);
        $('#edit_status').val(site.status);
        $('#edit_type').val(site.type);
        $('#edit_theme').val(site.theme);
        
        // Reset all checkboxes first
        $('.edit-category, .edit-country-checkbox, .edit-purpose, .edit-feature-checkbox').prop('checked', false);
        
        // Check global flag
        $('#edit_isGlobal').prop('checked', response.is_global);
        
        // Check selected categories
        if (response.site_categories && response.site_categories.length > 0) {
            response.site_categories.forEach(function(categoryId) {
                $('#edit_category' + categoryId).prop('checked', true);
            });
        }
        
        // Check selected countries
        if (response.site_countries && response.site_countries.length > 0) {
            response.site_countries.forEach(function(countryId) {
                $('#edit_country' + countryId).prop('checked', true);
            });
        }
        
        // Check selected purposes
        if (response.site_purposes && response.site_purposes.length > 0) {
            response.site_purposes.forEach(function(purposeId) {
                $('#edit_purpose' + purposeId).prop('checked', true);
            });
        }
        
        // Check selected features
        if (response.site_features && response.site_features.length > 0) {
            response.site_features.forEach(function(featureId) {
                $('#edit_feature' + featureId).prop('checked', true);
            });
        }
        
        // Trigger compatibility filtering for the Edit form
        if (typeof updateCompatibilityFiltering === 'function') {
            updateCompatibilityFiltering('edit');
        }
        
        // Update rating display
        if (typeof updateEditRating === 'function') {
            updateEditRating();
        }
    }
    
    /**
     * Confirm site deletion
     */
    function confirmDelete(id) {
        Swal.fire({
            title: 'Are you sure?',
            text: "This site will be permanently deleted!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                deleteSite(id);
            }
        });
    }
    
    /**
     * Delete site via AJAX
     */
    function deleteSite(id) {
        $.ajax({
            url: `/sites/${id}`,
            type: 'DELETE',
            success: function(response) {
                console.log('Delete site response:', response);
                
                if (response.status === 200) {
                    // Show success message
                    Swal.fire({
                        icon: 'success',
                        title: 'Deleted!',
                        text: response.message || 'Site deleted successfully'
                    });
                    
                    // Remove site row from table or reload page
                    $(`tr:has(button[value="${id}"])`).fadeOut(300, function() {
                        $(this).remove();
                        
                        // If no more sites, show message
                        if ($('tbody tr:visible').length === 0) {
                            $('#no-results').removeClass('d-none');
                        }
                    });
                } else {
                    // Show error message
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.message || 'Failed to delete site'
                    });
                }
            },
            error: function(xhr, status, error) {
                console.error('Error deleting site:', error);
                
                // Show error message
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed to delete site. Please try again.'
                });
            }
        });
    }
    
    /**
     * Submit the Add Site form via AJAX
     */
    function submitAddSiteForm() {
        console.log('Starting add site form submission');
        
        // Show loading state
        $('#saveSiteBtn').prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Saving...');
        
        // Clear previous error messages
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').empty();
        
        // Create a new FormData object
        const formData = new FormData();
        
        // Get form values manually to avoid duplicate entries
        formData.append('url', $('#url').val());
        formData.append('da', $('#da').val() || '');
        formData.append('description', $('#description').val() || '');
        formData.append('video_link', $('#video_link').val() || '');
        formData.append('status', $('#status').val());
        formData.append('type', $('#type').val());
        formData.append('theme', $('#theme').val());
        formData.append('complete_url', $('#complete_url').val() || '');
        
        // Add CSRF token
        formData.append('_token', $('meta[name="csrf-token"]').attr('content'));
        
        // Get checkbox values that might not be included in FormData if unchecked
        const isGlobal = $('#isGlobal').prop('checked');
        formData.append('is_global', isGlobal ? 1 : 0);
        
        // Get selected categories
        const categories = [];
        $('.add-category:checked').each(function() {
            categories.push($(this).val());
        });
        console.log('Selected categories:', categories);
        
        // Get selected countries
        const countries = [];
        $('.country-checkbox:checked, .add-country:checked').each(function() {
            const value = $(this).val();
            if (!countries.includes(value)) {
                countries.push(value);
            }
        });
        console.log('Selected countries:', countries);
        
        // Get selected purposes
        const purposes = [];
        $('.add-purpose:checked').each(function() {
            const value = $(this).val();
            if (!purposes.includes(value)) {
                purposes.push(value);
            }
        });
        console.log('Selected purposes:', purposes);
        
        // Get selected features
        const features = [];
        $('.feature-checkbox:checked, .add-feature:checked').each(function() {
            const value = $(this).val();
            if (!features.includes(value)) {
                features.push(value);
            }
        });
        console.log('Selected features:', features);
        
        // Add collected arrays to formData
        if (categories.length > 0) {
            // Remove duplicate categories
            const uniqueCategories = [...new Set(categories)];
            for (let i = 0; i < uniqueCategories.length; i++) {
                formData.append('categories[]', uniqueCategories[i]);
            }
        } else {
            console.warn('No categories selected');
        }
        
        if (countries.length > 0) {
            // Remove duplicate countries
            const uniqueCountries = [...new Set(countries)];
            for (let i = 0; i < uniqueCountries.length; i++) {
                formData.append('countries[]', uniqueCountries[i]);
            }
        } else if (!isGlobal) {
            console.warn('No countries selected and not global');
        }
        
        if (purposes.length > 0) {
            // Remove duplicate purposes
            const uniquePurposes = [...new Set(purposes)];
            for (let i = 0; i < uniquePurposes.length; i++) {
                formData.append('purposes[]', uniquePurposes[i]);
            }
        } else {
            console.warn('No purposes selected');
        }
        
        if (features.length > 0) {
            // Remove duplicate features
            const uniqueFeatures = [...new Set(features)];
            for (let i = 0; i < uniqueFeatures.length; i++) {
                formData.append('features[]', uniqueFeatures[i]);
            }
        } else {
            console.warn('No features selected');
        }
        
        // Log form data (for debugging)
        const formDataObj = {};
        for (let [key, value] of formData.entries()) {
            formDataObj[key] = value;
        }
        console.log('Form data:', formDataObj);
        
        // Send AJAX request
        $.ajax({
            url: '/sites',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                console.log('Add site response:', response);
                
                // Reset button state
                $('#saveSiteBtn').prop('disabled', false).html('Save changes');
                
                if (response.status === 400) {
                    // Handle validation errors
                    handleValidationErrors(response.errors);
                    return;
                }
                
                if (response.status === 200) {
                    // Show success message
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: response.message || 'Site added successfully!'
                    });
                    
                    // Close modal and reset form
                    $('#AddSiteModal').modal('hide');
                    $('#addSiteForm')[0].reset();
                    
                    // Reload page to show new site
                    setTimeout(function() {
                        window.location.reload();
                    }, 1000);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error adding site:', error);
                console.error('Status:', status);
                console.error('Response:', xhr.responseText);
                
                try {
                    const response = JSON.parse(xhr.responseText);
                    if (response.errors) {
                        handleValidationErrors(response.errors);
                    }
                } catch (e) {
                    console.error('Could not parse error response', e);
                }
                
                // Reset button state
                $('#saveSiteBtn').prop('disabled', false).html('Save changes');
                
                // Show error message
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed to add site. Please check console for details.'
                });
            }
        });
    }
    
    /**
     * Submit the Edit Site form via AJAX
     */
    function submitEditSiteForm() {
        // Show loading state
        $('.edit_btn').prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Saving...');
        
        // Clear previous error messages
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').empty();
        
        // Get site ID
        const siteId = $('#edit_id').val();
        
        // Create a new FormData object
        const formData = new FormData();
        
        // Get form values manually to avoid duplicate entries
        formData.append('url', $('#edit_url').val());
        formData.append('da', $('#edit_da').val() || '');
        formData.append('description', $('#edit_description').val() || '');
        formData.append('video_link', $('#edit_video_link').val() || '');
        formData.append('status', $('#edit_status').val());
        formData.append('type', $('#edit_type').val());
        formData.append('theme', $('#edit_theme').val());
        formData.append('complete_url', $('#edit_complete_url').val() || '');
        
        // Add CSRF token
        formData.append('_token', $('meta[name="csrf-token"]').attr('content'));
        
        // Get checkbox values that might not be included in FormData if unchecked
        const isGlobal = $('#edit_isGlobal').prop('checked');
        formData.append('is_global', isGlobal ? 1 : 0);
        
        // Add _method field for Laravel to handle PUT request
        formData.append('_method', 'PUT');
        
        // Get selected categories
        const categories = [];
        $('.edit-category:checked').each(function() {
            const value = $(this).val();
            if (!categories.includes(value)) {
                categories.push(value);
            }
        });
        
        // Get selected countries
        const countries = [];
        $('.edit-country-checkbox:checked, .edit-country:checked').each(function() {
            const value = $(this).val();
            if (!countries.includes(value)) {
                countries.push(value);
            }
        });
        
        // Get selected purposes
        const purposes = [];
        $('.edit-purpose:checked').each(function() {
            const value = $(this).val();
            if (!purposes.includes(value)) {
                purposes.push(value);
            }
        });
        
        // Get selected features
        const features = [];
        $('.edit-feature-checkbox:checked, .edit-feature:checked').each(function() {
            const value = $(this).val();
            if (!features.includes(value)) {
                features.push(value);
            }
        });
        
        // Add collected arrays to formData
        if (categories.length > 0) {
            // Use Set to ensure unique values
            const uniqueCategories = [...new Set(categories)];
            for (let i = 0; i < uniqueCategories.length; i++) {
                formData.append('categories[]', uniqueCategories[i]);
            }
        }
        
        if (countries.length > 0) {
            // Use Set to ensure unique values
            const uniqueCountries = [...new Set(countries)];
            for (let i = 0; i < uniqueCountries.length; i++) {
                formData.append('countries[]', uniqueCountries[i]);
            }
        }
        
        if (purposes.length > 0) {
            // Use Set to ensure unique values
            const uniquePurposes = [...new Set(purposes)];
            for (let i = 0; i < uniquePurposes.length; i++) {
                formData.append('purposes[]', uniquePurposes[i]);
            }
        }
        
        if (features.length > 0) {
            // Use Set to ensure unique values
            const uniqueFeatures = [...new Set(features)];
            for (let i = 0; i < uniqueFeatures.length; i++) {
                formData.append('features[]', uniqueFeatures[i]);
            }
        }
        
        // Send AJAX request
        $.ajax({
            url: '/sites/' + siteId,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                console.log('Edit site response:', response);
                
                // Reset button state
                $('.edit_btn').prop('disabled', false).html('Save changes');
                
                if (response.status === 400) {
                    // Handle validation errors
                    handleValidationErrors(response.errors, 'edit_');
                    return;
                }
                
                if (response.status === 200) {
                    // Show success message
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: response.message || 'Site updated successfully!'
                    });
                    
                    // Close modal
                    $('#EditModal').modal('hide');
                    
                    // Reload page to show updated site
                    setTimeout(function() {
                        window.location.reload();
                    }, 1000);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error updating site:', error);
                
                // Reset button state
                $('.edit_btn').prop('disabled', false).html('Save changes');
                
                // Show error message
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed to update site. Please try again.'
                });
            }
        });
    }
    
    /**
     * Handle validation errors from server response
     * @param {Object} errors - The validation errors object
     * @param {string} prefix - Optional prefix for field IDs
     */
    function handleValidationErrors(errors, prefix = '') {
        for (const field in errors) {
            const errorMsg = errors[field][0];
            const fieldName = field.replace(/\[\d+\]/g, ''); // Remove array indices if present
            const inputId = prefix + fieldName;
            
            // Set error class and message
            $(`#${inputId}`).addClass('is-invalid');
            $(`#${inputId}_error`).text(errorMsg);
            
            console.log(`Validation error for ${inputId}: ${errorMsg}`);
        }
    }
}); 
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
    
    // Add Site form submission
    $('#saveSiteBtn').on('click', function() {
        console.log('Save Site button clicked');
        submitAddSiteForm();
    });
    
    // Edit Site form submission
    $('.edit_btn').on('click', function() {
        console.log('Edit Site button clicked');
        submitEditSiteForm();
    });
    
    /**
     * Submit the Add Site form via AJAX
     */
    function submitAddSiteForm() {
        // Show loading state
        $('#saveSiteBtn').prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Saving...');
        
        // Clear previous error messages
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').empty();
        
        // Collect form data
        const formData = new FormData($('#addSiteForm')[0]);
        
        // Get checkbox values that might not be included in FormData if unchecked
        const isGlobal = $('#isGlobal').prop('checked');
        formData.append('is_global', isGlobal ? 1 : 0);
        
        // Get selected categories
        const categories = [];
        $('.add-category:checked').each(function() {
            categories.push($(this).val());
        });
        
        // Get selected countries
        const countries = [];
        $('.country-checkbox:checked').each(function() {
            countries.push($(this).val());
        });
        
        // Get selected purposes
        const purposes = [];
        $('.add-purpose:checked').each(function() {
            purposes.push($(this).val());
        });
        
        // Get selected features
        const features = [];
        $('.feature-checkbox:checked').each(function() {
            features.push($(this).val());
        });
        
        // Add collected arrays to formData
        if (categories.length > 0) {
            for (let i = 0; i < categories.length; i++) {
                formData.append('categories[]', categories[i]);
            }
        }
        
        if (countries.length > 0) {
            for (let i = 0; i < countries.length; i++) {
                formData.append('countries[]', countries[i]);
            }
        }
        
        if (purposes.length > 0) {
            for (let i = 0; i < purposes.length; i++) {
                formData.append('purposes[]', purposes[i]);
            }
        }
        
        if (features.length > 0) {
            for (let i = 0; i < features.length; i++) {
                formData.append('features[]', features[i]);
            }
        }
        
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
                
                // Reset button state
                $('#saveSiteBtn').prop('disabled', false).html('Save changes');
                
                // Show error message
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed to add site. Please try again.'
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
        
        // Collect form data
        const formData = new FormData($('#editSiteForm')[0]);
        
        // Get checkbox values that might not be included in FormData if unchecked
        const isGlobal = $('#edit_isGlobal').prop('checked');
        formData.append('is_global', isGlobal ? 1 : 0);
        
        // Add _method field for Laravel to handle PUT request
        formData.append('_method', 'PUT');
        
        // Get selected categories
        const categories = [];
        $('.edit-category:checked').each(function() {
            categories.push($(this).val());
        });
        
        // Get selected countries
        const countries = [];
        $('.edit-country-checkbox:checked').each(function() {
            countries.push($(this).val());
        });
        
        // Get selected purposes
        const purposes = [];
        $('.edit-purpose:checked').each(function() {
            purposes.push($(this).val());
        });
        
        // Get selected features
        const features = [];
        $('.edit-feature-checkbox:checked').each(function() {
            features.push($(this).val());
        });
        
        // Add collected arrays to formData
        if (categories.length > 0) {
            for (let i = 0; i < categories.length; i++) {
                formData.append('categories[]', categories[i]);
            }
        }
        
        if (countries.length > 0) {
            for (let i = 0; i < countries.length; i++) {
                formData.append('countries[]', countries[i]);
            }
        }
        
        if (purposes.length > 0) {
            for (let i = 0; i < purposes.length; i++) {
                formData.append('purposes[]', purposes[i]);
            }
        }
        
        if (features.length > 0) {
            for (let i = 0; i < features.length; i++) {
                formData.append('features[]', features[i]);
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
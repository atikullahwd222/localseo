/**
 * Edit Site Form Category Filtering
 * 
 * This script improves the category-based filtering of options in the Edit Site form
 */

$(document).ready(function() {
    console.log('Edit Site Category Filter JS loaded');
    
    // When a category is checked/unchecked in the edit form
    $(document).on('change', '.edit-category', function() {
        console.log('Category selection changed in Edit form');
        updateSelectedCategoriesBadges();
        filterCompatibleOptions();
    });
    
    // Direct binding for the toggle checkbox - higher priority
    $('#hideIncompatibleOptionsEdit').on('click', function() {
        const isChecked = $(this).is(':checked');
        console.log('Hide incompatible direct click in Edit form:', isChecked);
        toggleIncompatibleOptions(isChecked);
    });

    // Document level delegation as backup
    $(document).on('change', '#hideIncompatibleOptionsEdit', function() {
        const isChecked = $(this).is(':checked');
        console.log('Hide incompatible change event in Edit form:', isChecked);
        toggleIncompatibleOptions(isChecked);
    });
    
    // Function to toggle incompatible options visibility
    function toggleIncompatibleOptions(hide) {
        // Get all compatibility containers in the form
        const $containers = $('#editSiteForm .compatibility-container');
        
        console.log('Found', $containers.length, 'compatibility containers in Edit form');
        
        if (hide) {
            // Add class to containers
            $containers.addClass('hide-incompatible');
            
            // Also apply direct CSS to all unsupported options for redundancy
            $('#editSiteForm .form-check.unsupported-option').css({
                'display': 'none',
                'visibility': 'hidden',
                'opacity': '0'
            });
        } else {
            // Remove class from containers
            $containers.removeClass('hide-incompatible');
            
            // Reset direct CSS
            $('#editSiteForm .form-check.unsupported-option').css({
                'display': '',
                'visibility': 'visible',
                'opacity': '0.5'
            });
        }
        
        // Log the state of containers after change
        $containers.each(function(i) {
            console.log(`Edit form container ${i+1} classes:`, $(this).attr('class'));
        });
    }
    
    // Show compatibility note when categories are selected
    function updateSelectedCategoriesBadges() {
        const $container = $('#selectedCategoriesBadgesEditForm');
        $container.empty();
        
        const selectedCategories = [];
        $('.edit-category:checked').each(function() {
            selectedCategories.push({
                id: parseInt($(this).val()),
                name: $(this).data('category-name')
            });
        });
        
        console.log('Selected categories in Edit form:', selectedCategories);
        
        if (selectedCategories.length > 0) {
            $container.append('<div class="mb-1">Selected categories:</div>');
            selectedCategories.forEach(function(category) {
                $container.append(`<span class="badge bg-primary me-1 mb-1">${category.name}</span>`);
            });
            
            // Show compatibility notes
            $('#purposeCompatibilityNoteEdit, #countryCompatibilityNoteEdit, #featureCompatibilityNoteEdit').removeClass('d-none');
        } else {
            // Hide compatibility notes if no categories selected
            $('#purposeCompatibilityNoteEdit, #countryCompatibilityNoteEdit, #featureCompatibilityNoteEdit').addClass('d-none');
        }
    }
    
    // Filter compatible options based on selected categories
    function filterCompatibleOptions() {
        const selectedCategoryIds = [];
        $('.edit-category:checked').each(function() {
            selectedCategoryIds.push(parseInt($(this).val()));
        });
        
        if (selectedCategoryIds.length === 0) {
            // If no categories selected, reset all options
            console.log('No categories selected in Edit form, resetting all options');
            resetAllOptions();
            return;
        }
        
        // Add loading indicator
        const loadingHtml = '<div class="loading-indicator py-2 text-center"><div class="spinner-border spinner-border-sm text-primary me-2"></div><span>Loading compatible options...</span></div>';
        $('#selectedCategoriesBadgesEditForm').append(loadingHtml);
        
        // Get compatibility data from server
        $.ajax({
            url: routeUrls.compatibleOptions || '/sites/compatible-options',
            type: 'GET',
            data: {
                categories: selectedCategoryIds,
                option_types: ['countries', 'purposes', 'features']
            },
            dataType: 'json',
            success: function(response) {
                console.log('Compatibility response for Edit form:', response);
                $('.loading-indicator').remove();
                
                if (!response.success) {
                    console.error('Error in compatibility response for Edit form:', response.message || 'Unknown error');
                    return;
                }
                
                // Reset all options first to remove any previous marking
                resetAllOptions();
                
                // Process countries
                processCompatibleOptions('.edit-country', response.countries || []);
                
                // Process purposes
                processCompatibleOptions('.edit-purpose', response.purposes || []);
                
                // Process features
                processCompatibleOptions('.edit-feature', response.features || []);
                
                // Update rating based on feature changes
                if (typeof updateEditRating === 'function') {
                    updateEditRating();
                }
                
                // Check if hide incompatible options is checked
                const hideIncompatible = $('#hideIncompatibleOptionsEdit').is(':checked');
                console.log('Hide incompatible options is checked in Edit form:', hideIncompatible);
                
                // Apply hiding if needed
                if (hideIncompatible) {
                    toggleIncompatibleOptions(true);
                }
                
                // Update compatibility notes with counts
                updateCompatibilityNotes(response);
            },
            error: function(xhr, status, error) {
                console.error('AJAX error in Edit form:', status, error);
                $('.loading-indicator').remove();
                
                // Show error message
                $('#selectedCategoriesBadgesEditForm').append(
                    '<div class="alert alert-danger py-1 px-2 mt-2 error-message">' +
                    '<i class="fas fa-exclamation-triangle me-1"></i> Error loading compatibility data' +
                    '</div>'
                );
                
                // Remove error after 3 seconds
                setTimeout(function() {
                    $('.error-message').fadeOut(300, function() { $(this).remove(); });
                }, 3000);
            }
        });
    }
    
    // Process compatible options for a specific type
    function processCompatibleOptions(selector, compatibleIds) {
        if (!compatibleIds || compatibleIds.length === 0) {
            console.log(`No compatible options for ${selector} in Edit form`);
            // If no compatible options, mark all as unsupported
            $(selector).each(function() {
                $(this).prop('checked', false);
                const $formCheck = $(this).closest('.form-check');
                $formCheck.addClass('unsupported-option');
            });
            return;
        }
        
        console.log(`Processing ${compatibleIds.length} compatible options for ${selector} in Edit form`);
        
        // First mark all as unsupported
        $(selector).each(function() {
            const $formCheck = $(this).closest('.form-check');
            $formCheck.addClass('unsupported-option');
        });
        
        // Then mark compatible ones
        $(selector).each(function() {
            const optionId = parseInt($(this).val());
            const $option = $(this);
            const $formCheck = $option.closest('.form-check');
            
            if (compatibleIds.includes(optionId)) {
                // This option is compatible
                $formCheck.removeClass('unsupported-option');
                console.log(`Marked as compatible in Edit form: ${selector} id=${optionId}`);
            } else {
                // This option is not compatible with selected categories
                $option.prop('checked', false); // Uncheck it
                $formCheck.addClass('unsupported-option'); // Mark as unsupported
                console.log(`Marked as unsupported in Edit form: ${selector} id=${optionId}`);
            }
        });
        
        // Extra log to verify unsupported options
        console.log(`Number of unsupported ${selector} options in Edit form:`, $(selector).closest('.form-check.unsupported-option').length);
    }
    
    // Reset all options to default state
    function resetAllOptions() {
        $('.edit-country, .edit-purpose, .edit-feature').closest('.form-check').removeClass('unsupported-option');
    }
    
    // Update compatibility notes with counts
    function updateCompatibilityNotes(response) {
        const countryCount = response.countries ? response.countries.length : 0;
        const purposeCount = response.purposes ? response.purposes.length : 0;
        const featureCount = response.features ? response.features.length : 0;
        
        $('#countryCompatibilityNoteEdit').html(
            `<i class="fas fa-info-circle me-1"></i> Showing ${countryCount} compatible countries`
        ).removeClass('d-none');
        
        $('#purposeCompatibilityNoteEdit').html(
            `<i class="fas fa-info-circle me-1"></i> Showing ${purposeCount} compatible work purposes`
        ).removeClass('d-none');
        
        $('#featureCompatibilityNoteEdit').html(
            `<i class="fas fa-info-circle me-1"></i> Showing ${featureCount} compatible features`
        ).removeClass('d-none');
    }
    
    // Initialize form when the modal is shown
    $('#EditModal').on('shown.bs.modal', function() {
        console.log('Edit Site modal shown - checking existing categories');
        
        // If categories are already selected, filter options
        if ($('.edit-category:checked').length > 0) {
            setTimeout(function() {
                updateSelectedCategoriesBadges();
                filterCompatibleOptions();
            }, 500);
        }
    });
}); 
/**
 * Site Category Compatibility Management
 * 
 * This script enhances the site forms to filter countries, purposes, and features
 * based on the compatibility with selected site categories.
 */

$(document).ready(function() {
    // Logging to verify initialization
    console.log('Site compatibility JS loaded');
    
    // Listen for category changes in the add form
    $(document).on('change', '.add-category', function() {
        console.log('Add form category changed');
        updateCompatibilityFiltering('add');
    });
    
    // Listen for category changes in the edit form
    $(document).on('change', '.edit-category', function() {
        console.log('Edit form category changed');
        updateCompatibilityFiltering('edit');
    });
    
    // Function to update compatibility filtering based on form type
    function updateCompatibilityFiltering(formType) {
        console.log('Updating compatibility filtering for', formType, 'form');
        const prefix = formType === 'edit' ? 'edit_' : '';
        const categoryClass = formType === 'edit' ? '.edit-category' : '.add-category';
        const countryClass = formType === 'edit' ? '.edit-country' : '.add-country';
        const purposeClass = formType === 'edit' ? '.edit-purpose' : '.add-purpose';
        const featureClass = formType === 'edit' ? '.edit-feature' : '.add-feature';
        
        // Get selected categories
        const selectedCategories = [];
        $(categoryClass + ':checked').each(function() {
            selectedCategories.push(parseInt($(this).val()));
        });
        
        console.log('Selected categories:', selectedCategories);
        
        // Clear previous error messages
        $('#compatibilityError').remove();
        
        // Define badge container ID correctly
        const badgeContainer = formType === 'edit' ? 'selectedCategoriesBadgesEditForm' : 'selectedCategoriesBadgesAddForm';
        
        // Update badge display
        updateCategoryBadges('#' + badgeContainer, selectedCategories);
        
        // Show visual loading indicator while fetching compatibility data
        const loadingIndicator = $('<div class="loading-indicator py-2"><div class="spinner-border spinner-border-sm text-primary me-2"></div><span>Loading compatible options...</span></div>');
        $('#' + badgeContainer).append(loadingIndicator);
        
        if (selectedCategories.length > 0) {
            // Highlight sections that will be filtered
            $('#' + prefix + 'countriesContainer').closest('.border').addClass('compatibility-active');
            $(purposeClass + ', ' + featureClass).closest('.border').addClass('compatibility-active');
            
            // Get compatible options via AJAX - use the route path from the page
            $.ajax({
                url: routeUrls.compatibleOptions || '/sites/compatible-options', // Use global variable if available
                type: 'GET',
                data: {
                    categories: selectedCategories,
                    option_types: ['countries', 'purposes', 'features'] // Use the expected array format
                },
                dataType: 'json',
                success: function(response) {
                    console.log('Compatibility API response:', response);
                    loadingIndicator.remove();
                    
                    if (response.success) {
                        // Update compatibility visuals
                        updateCompatibilityVisuals(response.countries || [], countryClass, '#' + prefix + 'countryCompatibilityNote');
                        updateCompatibilityVisuals(response.purposes || [], purposeClass, '#' + prefix + 'purposeCompatibilityNote');
                        updateCompatibilityVisuals(response.features || [], featureClass, '#' + prefix + 'featureCompatibilityNote');
                        
                        // Show clear indicator of which categories are filtering
                        const categoryNames = $(categoryClass + ':checked').map(function() {
                            return $(this).data('category-name');
                        }).get().join(', ');
                        
                        $('#' + prefix + 'compatibilityInfoNote').remove();
                        $('#' + badgeContainer).append(
                            `<div id="${prefix}compatibilityInfoNote" class="alert alert-info py-1 px-2 mt-2">
                                <i class="fas fa-filter me-1"></i>
                                <strong>Filtering options:</strong> Showing items compatible with "${categoryNames}"
                            </div>`
                        );
                        
                        // Apply hide-incompatible class if the option is checked
                        if (formType === 'edit') {
                            if ($('#hideIncompatibleOptionsEdit').is(':checked')) {
                                console.log('Auto-applying hide-incompatible in edit form');
                                $('#editSiteForm .compatibility-container').addClass('hide-incompatible');
                                $('#editSiteForm .form-check.unsupported-option').hide();
                            }
                        } else {
                            if ($('#hideIncompatibleOptions').is(':checked')) {
                                console.log('Auto-applying hide-incompatible in add form');
                                $('#addSiteForm .compatibility-container').addClass('hide-incompatible');
                                $('#addSiteForm .form-check.unsupported-option').hide();
                            }
                        }
                    } else {
                        showCompatibilityError(badgeContainer, response.message || 'Unknown error occurred');
                    }
                },
                error: function(xhr) {
                    console.error('Compatibility filtering error:', xhr.responseText);
                    showCompatibilityError(badgeContainer, 'Failed to load compatibility data');
                    loadingIndicator.remove();
                    
                    // Reset on error
                    resetCompatibility(countryClass, purposeClass, featureClass, prefix);
                }
            });
        } else {
            // Reset when no categories selected
            resetCompatibility(countryClass, purposeClass, featureClass, prefix);
            loadingIndicator.remove();
        }
    }
    
    // Update visual elements for compatibility filtering
    function updateCompatibilityVisuals(compatibleIds, elementClass, noteSelector) {
        // Reset all items first
        $(elementClass).prop('disabled', false).closest('.form-check').removeClass('unsupported-option');
        
        if (compatibleIds && compatibleIds.length > 0) {
            console.log('Processing compatibility for', elementClass, '- Compatible IDs:', compatibleIds);
            
            // Mark incompatible options
            $(elementClass).each(function() {
                const itemId = parseInt($(this).val());
                if (!compatibleIds.includes(itemId)) {
                    $(this).prop('checked', false);
                    // Apply class to the form-check parent
                    const $formCheck = $(this).closest('.form-check');
                    $formCheck.addClass('unsupported-option');
                    console.log('Marked as unsupported:', itemId, $formCheck);
                }
            });
            $(noteSelector).removeClass('d-none');
        } else {
            $(noteSelector).addClass('d-none');
        }
    }
    
    // Reset compatibility filtering
    function resetCompatibility(countryClass, purposeClass, featureClass, prefix) {
        $(countryClass + ', ' + purposeClass + ', ' + featureClass)
            .prop('disabled', false).closest('.form-check').removeClass('unsupported-option');
        
        $('#' + prefix + 'countriesContainer').closest('.border').removeClass('compatibility-active');
        $(purposeClass + ', ' + featureClass).closest('.border').removeClass('compatibility-active');
            
        $('#' + prefix + 'countryCompatibilityNote, #' + prefix + 'purposeCompatibilityNote, #' + prefix + 'featureCompatibilityNote')
            .addClass('d-none');
            
        $('#' + prefix + 'compatibilityInfoNote').remove();
    }
    
    // Show compatibility error message
    function showCompatibilityError(container, message) {
        $('#' + container).append(
            `<div id="compatibilityError" class="alert alert-danger py-1 px-2 mt-2">
                <i class="fas fa-exclamation-triangle me-1"></i> ${message}
            </div>`
        );
        
        // Auto-remove after 5 seconds
        setTimeout(function() {
            $('#compatibilityError').fadeOut(300, function() { $(this).remove(); });
        }, 5000);
    }
    
    // Update category badges display
    function updateCategoryBadges(container, selectedCategories) {
        const $container = $(container);
        $container.empty();
        
        if (selectedCategories && selectedCategories.length > 0) {
            $container.append('<div class="mb-1">Selected categories:</div>');
            selectedCategories.forEach(function(categoryId) {
                const $category = $('#category' + categoryId + ', #edit_category' + categoryId);
                if ($category.length) {
                    const name = $category.data('category-name');
                    $container.append(`<span class="badge bg-primary me-1 mb-1">${name}</span>`);
                }
            });
        }
    }
    
    // Make this function globally available for use in other scripts
    window.updateCompatibilityFiltering = updateCompatibilityFiltering;
    
    // Reset everything when modals are closed
    $('#AddSiteModal').on('hidden.bs.modal', function() {
        resetCompatibility('.add-country', '.add-purpose', '.add-feature', '');
        $('#selectedCategoriesBadgesAddForm').empty();
    });
    
    $('#EditModal').on('hidden.bs.modal', function() {
        resetCompatibility('.edit-country', '.edit-purpose', '.edit-feature', 'edit_');
        $('#selectedCategoriesBadgesEditForm').empty();
    });
    
    // Direct handler for hide incompatible options toggle in add form
    $(document).on('click', '#hideIncompatibleOptions', function() {
        const isChecked = $(this).is(':checked');
        console.log('Hide incompatible options toggled in add form:', isChecked);
        
        $('#addSiteForm .compatibility-container').each(function() {
            if (isChecked) {
                $(this).addClass('hide-incompatible');
                $('#addSiteForm .form-check.unsupported-option').hide();
            } else {
                $(this).removeClass('hide-incompatible');
                $('#addSiteForm .form-check.unsupported-option').show().css({
                    'opacity': '0.5',
                    'text-decoration': 'line-through'
                });
            }
        });
    });
    
    // Direct handler for hide incompatible options toggle in edit form
    $(document).on('click', '#hideIncompatibleOptionsEdit', function() {
        const isChecked = $(this).is(':checked');
        console.log('Hide incompatible options toggled in edit form:', isChecked);
        
        $('#editSiteForm .compatibility-container').each(function() {
            if (isChecked) {
                $(this).addClass('hide-incompatible');
                $('#editSiteForm .form-check.unsupported-option').hide();
            } else {
                $(this).removeClass('hide-incompatible');
                $('#editSiteForm .form-check.unsupported-option').show().css({
                    'opacity': '0.5',
                    'text-decoration': 'line-through'
                });
            }
        });
    });
    
    // Run initial compatibility filtering on page load with a slight delay
    setTimeout(function() {
        // Check if we're in the add or edit form by looking for inputs
        if ($('.add-category').length > 0) {
            console.log('Running initial compatibility filtering for add form');
            updateCompatibilityFiltering('add');
        }
        
        if ($('.edit-category').length > 0) {
            console.log('Running initial compatibility filtering for edit form');
            updateCompatibilityFiltering('edit');
        }
    }, 500);
}); 
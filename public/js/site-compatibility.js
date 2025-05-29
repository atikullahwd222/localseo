/**
 * Site Category Compatibility Management
 * 
 * This script enhances the site forms to filter countries, purposes, and features
 * based on the compatibility with selected site categories.
 */

$(document).ready(function() {
    // Listen for category changes in the add form
    $(document).on('change', '.add-category', function() {
        updateCompatibilityFiltering('add');
    });
    
    // Listen for category changes in the edit form
    $(document).on('change', '.edit-category', function() {
        updateCompatibilityFiltering('edit');
    });
    
    // Function to update compatibility filtering based on form type
    function updateCompatibilityFiltering(formType) {
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
        
        // Clear previous error messages
        $('#compatibilityError').remove();
        
        // Update badge display
        updateCategoryBadges('#' + prefix + 'selectedCategoriesBadgesForm', selectedCategories);
        
        // Show visual loading indicator while fetching compatibility data
        const loadingIndicator = $('<div class="loading-indicator py-2"><div class="spinner-border spinner-border-sm text-primary me-2"></div><span>Loading compatible options...</span></div>');
        $('#' + prefix + 'selectedCategoriesBadgesForm').append(loadingIndicator);
        
        if (selectedCategories.length > 0) {
            // Highlight sections that will be filtered
            $('#' + prefix + 'countriesContainer').closest('.border').addClass('compatibility-active');
            $(purposeClass + ', ' + featureClass).closest('.border').addClass('compatibility-active');
            
            // Get compatible options via AJAX
            $.ajax({
                url: '/sites/compatible-options',
                type: 'GET',
                data: {
                    categories: selectedCategories,
                    option_type: 'all'
                },
                success: function(response) {
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
                        $('#' + prefix + 'selectedCategoriesBadgesForm').append(
                            `<div id="${prefix}compatibilityInfoNote" class="alert alert-info py-1 px-2 mt-2">
                                <i class="fas fa-filter me-1"></i>
                                <strong>Filtering options:</strong> Showing items compatible with "${categoryNames}"
                            </div>`
                        );
                    } else {
                        showCompatibilityError(prefix + 'selectedCategoriesBadgesForm', response.message);
                    }
                    loadingIndicator.remove();
                },
                error: function(xhr) {
                    showCompatibilityError(prefix + 'selectedCategoriesBadgesForm', 'Failed to load compatibility data');
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
        
        if (compatibleIds.length > 0) {
            // Mark incompatible options
            $(elementClass).each(function() {
                const itemId = parseInt($(this).val());
                if (!compatibleIds.includes(itemId)) {
                    $(this).prop('disabled', true).prop('checked', false)
                        .closest('.form-check').addClass('unsupported-option');
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
        
        $('#' + prefix + 'countriesContainer, ' + purposeClass + ', ' + featureClass)
            .closest('.border').removeClass('compatibility-active');
            
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
        
        if (selectedCategories.length > 0) {
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
    
    // Reset everything when modals are closed
    $('#AddSiteModal').on('hidden.bs.modal', function() {
        resetCompatibility('.add-country', '.add-purpose', '.add-feature', '');
        $('#selectedCategoriesBadgesForm').empty();
    });
    
    $('#EditModal').on('hidden.bs.modal', function() {
        resetCompatibility('.edit-country', '.edit-purpose', '.edit-feature', 'edit_');
        $('#edit_selectedCategoriesBadgesForm').empty();
    });
}); 
/**
 * Edit Site Form Category Filtering
 * 
 * This script improves the category-based filtering of options in the Edit Site form
 */

$(document).ready(function() {
    console.log('Edit Site Category Filter JS loaded');
    
    // Initial badges and state
    updateSelectedCategoriesBadgesEdit();
    
    // Force initial filtering on page load - with a delay to ensure all elements are loaded
    setTimeout(function() {
        console.log('Checking for pre-selected categories in Edit form...');
        const selectedCategories = $('.edit-category:checked');
        console.log('Found ' + selectedCategories.length + ' pre-selected categories');
        
        if (selectedCategories.length > 0) {
            console.log('Applying initial filtering for pre-selected categories in Edit form');
            // Use the global function from site-compatibility.js
            if (typeof updateCompatibilityFiltering === 'function') {
                updateCompatibilityFiltering('edit');
            } else {
                console.error('updateCompatibilityFiltering function not found');
                filterCompatibleOptions(); // Fallback to local function
            }
            
            // Apply hiding incompatible options on initialization
            toggleIncompatibleOptions($('#hideIncompatibleOptionsEdit').is(':checked'));
        }
    }, 800);
    
    // When a category is checked/unchecked in the edit form
    $('.edit-category').on('change', function() {
        console.log('Category selection changed in Edit form (direct binding)');
        updateSelectedCategoriesBadgesEdit();
        
        // Use the global function from site-compatibility.js
        if (typeof updateCompatibilityFiltering === 'function') {
            updateCompatibilityFiltering('edit');
        } else {
            console.error('updateCompatibilityFiltering function not found');
            filterCompatibleOptions(); // Fallback to local function
        }
    });
    
    // Additional delegation binding for dynamically added elements
    $(document).on('change', '.edit-category', function() {
        console.log('Category selection changed in Edit form (delegation)');
        updateSelectedCategoriesBadgesEdit();
        
        // Use the global function from site-compatibility.js
        if (typeof updateCompatibilityFiltering === 'function') {
            updateCompatibilityFiltering('edit');
        } else {
            console.error('updateCompatibilityFiltering function not found');
            filterCompatibleOptions(); // Fallback to local function
        }
    });
    
    // Direct binding for the toggle checkbox
    $('#hideIncompatibleOptionsEdit').on('click', function() {
        const isChecked = $(this).is(':checked');
        console.log('Hide incompatible edit form direct click:', isChecked);
        toggleIncompatibleOptions(isChecked);
    });

    // Document level delegation as backup
    $(document).on('change', '#hideIncompatibleOptionsEdit', function() {
        const isChecked = $(this).is(':checked');
        console.log('Hide incompatible edit form change event:', isChecked);
        toggleIncompatibleOptions(isChecked);
    });
    
    // Function to toggle incompatible options visibility
    function toggleIncompatibleOptions(hide) {
        // Get all compatibility containers in the form
        const $containers = $('#editSiteForm .compatibility-container');
        
        console.log('Found', $containers.length, 'compatibility containers in edit form');
        
        if (hide) {
            // Add class to containers
            $containers.addClass('hide-incompatible');
            
            // Also apply direct CSS to all unsupported options for redundancy
            $('#editSiteForm .form-check.unsupported-option').css({
                'display': 'none',
                'visibility': 'hidden'
            });
            
            // Make sure supported options are visible
            $('#editSiteForm .form-check:not(.unsupported-option)').css({
                'display': '',
                'visibility': 'visible',
                'opacity': '1'
            });
        } else {
            // Remove class from containers
            $containers.removeClass('hide-incompatible');
            
            // Reset direct CSS for unsupported options
            $('#editSiteForm .form-check.unsupported-option').css({
                'display': '',
                'visibility': 'visible',
                'opacity': '0.5',
                'text-decoration': 'line-through'
            });
            
            // Make sure all options are visible
            $('#editSiteForm .form-check').css({
                'display': '',
                'visibility': 'visible'
            });
        }
        
        // Debug counts of visible and hidden options
        console.log('Visible options in edit form:', $('#editSiteForm .form-check:visible').length);
        console.log('Hidden options in edit form:', $('#editSiteForm .form-check:hidden').length);
    }
    
    // Show compatibility note when categories are selected
    function updateSelectedCategoriesBadgesEdit() {
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
    // This is a fallback method in case the global function isn't available
    function filterCompatibleOptions() {
        const selectedCategoryIds = [];
        $('.edit-category:checked').each(function() {
            selectedCategoryIds.push(parseInt($(this).val()));
        });
        
        console.log('Filtering compatible options based on selected categories in Edit form:', selectedCategoryIds);
        
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
                
                // Make sure compatible options are visible
                $('#editSiteForm .form-check:not(.unsupported-option)').css({
                    'opacity': '1',
                    'font-weight': 'normal',
                    'visibility': 'visible',
                    'display': ''
                });
                
                // Check if hide incompatible options is checked
                const hideIncompatible = $('#hideIncompatibleOptionsEdit').is(':checked');
                console.log('Hide incompatible options is checked in Edit form:', hideIncompatible);
                
                // Apply hiding if needed
                if (hideIncompatible) {
                    toggleIncompatibleOptions(true);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error fetching compatible options for Edit form:', error);
                $('.loading-indicator').remove();
                
                // Show error message
                $('#selectedCategoriesBadgesEditForm').append(
                    `<div class="alert alert-danger py-1 px-2 mt-2 compatibility-error">
                        <i class="fas fa-exclamation-triangle me-1"></i>
                        Failed to load compatibility data: ${error}
                    </div>`
                );
                
                // Auto-remove after 5 seconds
                setTimeout(function() {
                    $('.compatibility-error').fadeOut(300, function() { $(this).remove(); });
                }, 5000);
            }
        });
    }
    
    // Helper function to process compatible options
    function processCompatibleOptions(selector, compatibleIds) {
        console.log('Processing compatible', selector, 'with IDs:', compatibleIds);
        
        // Store which ones are marked as incompatible to prevent duplicate processing
        const processedIncompatible = new Set();
        
        if (compatibleIds && compatibleIds.length > 0) {
            // Mark all incompatible options
            $(selector).each(function() {
                const itemId = parseInt($(this).val());
                if (!compatibleIds.includes(itemId) && !processedIncompatible.has(itemId)) {
                    // Add to processed set
                    processedIncompatible.add(itemId);
                    
                    // Apply styling to the parent form-check
                    const $formCheck = $(this).closest('.form-check');
                    $formCheck.addClass('unsupported-option');
                    
                    // Uncheck if it was checked
                    if ($(this).is(':checked')) {
                        $(this).prop('checked', false);
                        console.log('Unchecked incompatible option in Edit form:', itemId);
                    }
                }
            });
            
            // Add compatibility note
            const containerSelector = selector === '.edit-country' ? '#countryCompatibilityNoteEdit' :
                        selector === '.edit-purpose' ? '#purposeCompatibilityNoteEdit' : 
                        '#featureCompatibilityNoteEdit';
            
            $(containerSelector).removeClass('d-none');
        }
    }
    
    // Reset all options to default state
    function resetAllOptions() {
        // Remove unsupported-option class from all form-checks
        $('#editSiteForm .form-check').removeClass('unsupported-option').css({
            'opacity': '1',
            'text-decoration': 'none',
            'display': '',
            'visibility': 'visible'
        });
        
        // Enable all checkboxes
        $('.edit-country, .edit-purpose, .edit-feature').prop('disabled', false);
        
        // Hide compatibility notes
        $('#countryCompatibilityNoteEdit, #purposeCompatibilityNoteEdit, #featureCompatibilityNoteEdit').addClass('d-none');
    }
}); 
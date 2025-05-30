/**
 * Add Site Form Category Filtering
 * 
 * This script improves the category-based filtering of options in the Add Site form
 */

$(document).ready(function() {
    console.log('Add Site Category Filter JS loaded');
    
    // Initial badges and state
    updateSelectedCategoriesBadges();
    
    // Force initial filtering on page load - with a delay to ensure all elements are loaded
    setTimeout(function() {
        console.log('Checking for pre-selected categories...');
        const selectedCategories = $('.add-category:checked');
        console.log('Found ' + selectedCategories.length + ' pre-selected categories');
        
        if (selectedCategories.length > 0) {
            console.log('Applying initial filtering for pre-selected categories');
            // Use the global function from site-compatibility.js
            if (typeof updateCompatibilityFiltering === 'function') {
                updateCompatibilityFiltering('add');
            } else {
                console.error('updateCompatibilityFiltering function not found');
                filterCompatibleOptions(); // Fallback to local function
            }
            
            // Apply hiding incompatible options on initialization
            toggleIncompatibleOptions($('#hideIncompatibleOptions').is(':checked'));
        }
    }, 800);
    
    // When a category is checked/unchecked in the add form - use both direct binding and delegation
    $('.add-category').on('change', function() {
        console.log('Category selection changed in Add form (direct binding)');
        updateSelectedCategoriesBadges();
        
        // Use the global function from site-compatibility.js
        if (typeof updateCompatibilityFiltering === 'function') {
            updateCompatibilityFiltering('add');
        } else {
            console.error('updateCompatibilityFiltering function not found');
            filterCompatibleOptions(); // Fallback to local function
        }
    });
    
    // Additional delegation binding for dynamically added elements
    $(document).on('change', '.add-category', function() {
        console.log('Category selection changed in Add form (delegation)');
        updateSelectedCategoriesBadges();
        
        // Use the global function from site-compatibility.js
        if (typeof updateCompatibilityFiltering === 'function') {
            updateCompatibilityFiltering('add');
        } else {
            console.error('updateCompatibilityFiltering function not found');
            filterCompatibleOptions(); // Fallback to local function
        }
    });
    
    // Direct binding for the toggle checkbox - higher priority
    $('#hideIncompatibleOptions').on('click', function() {
        const isChecked = $(this).is(':checked');
        console.log('Hide incompatible direct click:', isChecked);
        toggleIncompatibleOptions(isChecked);
    });

    // Document level delegation as backup
    $(document).on('change', '#hideIncompatibleOptions', function() {
        const isChecked = $(this).is(':checked');
        console.log('Hide incompatible change event:', isChecked);
        toggleIncompatibleOptions(isChecked);
    });
    
    // Function to toggle incompatible options visibility
    function toggleIncompatibleOptions(hide) {
        // Get all compatibility containers in the form
        const $containers = $('#addSiteForm .compatibility-container');
        
        console.log('Found', $containers.length, 'compatibility containers');
        
        if (hide) {
            // Add class to containers
            $containers.addClass('hide-incompatible');
            
            // Also apply direct CSS to all unsupported options for redundancy
            $('#addSiteForm .form-check.unsupported-option').css({
                'display': 'none',
                'visibility': 'hidden'
            });
            
            // Make sure supported options are visible
            $('#addSiteForm .form-check:not(.unsupported-option)').css({
                'display': '',
                'visibility': 'visible',
                'opacity': '1'
            });
        } else {
            // Remove class from containers
            $containers.removeClass('hide-incompatible');
            
            // Reset direct CSS for unsupported options
            $('#addSiteForm .form-check.unsupported-option').css({
                'display': '',
                'visibility': 'visible',
                'opacity': '0.5',
                'text-decoration': 'line-through'
            });
            
            // Make sure all options are visible
            $('#addSiteForm .form-check').css({
                'display': '',
                'visibility': 'visible'
            });
        }
        
        // Debug counts of visible and hidden options
        console.log('Visible options:', $('#addSiteForm .form-check:visible').length);
        console.log('Hidden options:', $('#addSiteForm .form-check:hidden').length);
    }
    
    // Show compatibility note when categories are selected
    function updateSelectedCategoriesBadges() {
        const $container = $('#selectedCategoriesBadgesAddForm');
        $container.empty();
        
        const selectedCategories = [];
        $('.add-category:checked').each(function() {
            selectedCategories.push({
                id: parseInt($(this).val()),
                name: $(this).data('category-name')
            });
        });
        
        console.log('Selected categories:', selectedCategories);
        
        if (selectedCategories.length > 0) {
            $container.append('<div class="mb-1">Selected categories:</div>');
            selectedCategories.forEach(function(category) {
                $container.append(`<span class="badge bg-primary me-1 mb-1">${category.name}</span>`);
            });
            
            // Show compatibility notes
            $('#purposeCompatibilityNoteAdd, #countryCompatibilityNoteAdd, #featureCompatibilityNoteAdd').removeClass('d-none');
        } else {
            // Hide compatibility notes if no categories selected
            $('#purposeCompatibilityNoteAdd, #countryCompatibilityNoteAdd, #featureCompatibilityNoteAdd').addClass('d-none');
        }
    }
    
    // Filter compatible options based on selected categories
    // This is a fallback method in case the global function isn't available
    function filterCompatibleOptions() {
        const selectedCategoryIds = [];
        $('.add-category:checked').each(function() {
            selectedCategoryIds.push(parseInt($(this).val()));
        });
        
        console.log('Filtering compatible options based on selected categories:', selectedCategoryIds);
        
        if (selectedCategoryIds.length === 0) {
            // If no categories selected, reset all options
            console.log('No categories selected, resetting all options');
            resetAllOptions();
            return;
        }
        
        // Add loading indicator
        const loadingHtml = '<div class="loading-indicator py-2 text-center"><div class="spinner-border spinner-border-sm text-primary me-2"></div><span>Loading compatible options...</span></div>';
        $('#selectedCategoriesBadgesAddForm').append(loadingHtml);
        
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
                console.log('Compatibility response:', response);
                $('.loading-indicator').remove();
                
                if (!response.success) {
                    console.error('Error in compatibility response:', response.message || 'Unknown error');
                    return;
                }
                
                // Reset all options first to remove any previous marking
                resetAllOptions();
                
                // Process countries
                processCompatibleOptions('.add-country', response.countries || []);
                
                // Process purposes
                processCompatibleOptions('.add-purpose', response.purposes || []);
                
                // Process features
                processCompatibleOptions('.add-feature', response.features || []);
                
                // Update rating based on feature changes
                if (typeof updateRating === 'function') {
                    updateRating();
                }
                
                // Make sure compatible options are visible
                $('#addSiteForm .form-check:not(.unsupported-option)').css({
                    'opacity': '1',
                    'font-weight': 'normal',
                    'visibility': 'visible',
                    'display': ''
                });
                
                // Check if hide incompatible options is checked
                const hideIncompatible = $('#hideIncompatibleOptions').is(':checked');
                console.log('Hide incompatible options is checked:', hideIncompatible);
                
                // Apply hiding if needed
                if (hideIncompatible) {
                    toggleIncompatibleOptions(true);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error fetching compatible options:', error);
                $('.loading-indicator').remove();
                
                // Show error message
                $('#selectedCategoriesBadgesAddForm').append(
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
                        console.log('Unchecked incompatible option:', itemId);
                    }
                }
            });
            
            // Add compatibility note
            const containerSelector = selector === '.add-country' ? '#countryCompatibilityNoteAdd' :
                        selector === '.add-purpose' ? '#purposeCompatibilityNoteAdd' : 
                        '#featureCompatibilityNoteAdd';
            
            $(containerSelector).removeClass('d-none');
        }
    }
    
    // Reset all options to default state
    function resetAllOptions() {
        // Remove unsupported-option class from all form-checks
        $('#addSiteForm .form-check').removeClass('unsupported-option').css({
            'opacity': '1',
            'text-decoration': 'none',
            'display': '',
            'visibility': 'visible'
        });
        
        // Enable all checkboxes
        $('.add-country, .add-purpose, .add-feature').prop('disabled', false);
        
        // Hide compatibility notes
        $('#countryCompatibilityNoteAdd, #purposeCompatibilityNoteAdd, #featureCompatibilityNoteAdd').addClass('d-none');
    }
}); 
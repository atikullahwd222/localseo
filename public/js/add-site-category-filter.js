/**
 * Add Site Form Category Filtering
 * 
 * This script improves the category-based filtering of options in the Add Site form
 */

$(document).ready(function() {
    console.log('Add Site Category Filter JS loaded');
    
    // Initial badges and state
    updateSelectedCategoriesBadges();
    
    // Force initial filtering on page load - with a longer delay to ensure all elements are loaded
    setTimeout(function() {
        console.log('Checking for pre-selected categories...');
        const selectedCategories = $('.add-category:checked');
        console.log('Found ' + selectedCategories.length + ' pre-selected categories');
        
        if (selectedCategories.length > 0) {
            console.log('Applying initial filtering for pre-selected categories');
            filterCompatibleOptions();
            // Apply hiding incompatible options on initialization since checkbox is checked by default
            toggleIncompatibleOptions($('#hideIncompatibleOptions').is(':checked'));
        }
    }, 800);
    
    // When a category is checked/unchecked in the add form - use both direct binding and delegation
    $('.add-category').on('change', function() {
        console.log('Category selection changed in Add form (direct binding)');
        updateSelectedCategoriesBadges();
        filterCompatibleOptions();
    });
    
    // Additional delegation binding for dynamically added elements
    $(document).on('change', '.add-category', function() {
        console.log('Category selection changed in Add form (delegation)');
        updateSelectedCategoriesBadges();
        filterCompatibleOptions();
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
    function filterCompatibleOptions() {
        const selectedCategoryIds = [];
        $('.add-category:checked').each(function() {
            selectedCategoryIds.push(parseInt($(this).val()));
        });
        
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
                    'font-weight': 'normal'
                });
                
                // Check if hide incompatible options is checked
                const hideIncompatible = $('#hideIncompatibleOptions').is(':checked');
                console.log('Hide incompatible options is checked:', hideIncompatible);
                
                // Apply hiding if needed
                if (hideIncompatible) {
                    toggleIncompatibleOptions(true);
                } else {
                    // Just style the unsupported options
                    $('#addSiteForm .form-check.unsupported-option').css({
                        'opacity': '0.5',
                        'text-decoration': 'line-through'
                    });
                }
                
                // Update compatibility notes with counts
                updateCompatibilityNotes(response);
            },
            error: function(xhr, status, error) {
                console.error('AJAX error:', status, error);
                $('.loading-indicator').remove();
                
                // Show error message
                $('#selectedCategoriesBadgesAddForm').append(
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
            console.log(`No compatible options for ${selector}`);
            // If no compatible options, mark all as unsupported
            $(selector).each(function() {
                $(this).prop('checked', false);
                const $formCheck = $(this).closest('.form-check');
                $formCheck.addClass('unsupported-option');
            });
            return;
        }
        
        console.log(`Processing ${compatibleIds.length} compatible options for ${selector}`);
        
        // First mark all as unsupported
        $(selector).each(function() {
            const $formCheck = $(this).closest('.form-check');
            $formCheck.addClass('unsupported-option');
            // Make sure it's unchecked
            $(this).prop('checked', false);
        });
        
        // Then mark compatible ones and ensure uniqueness
        const processedIds = new Set(); // Keep track of processed IDs to avoid duplicates
        
        $(selector).each(function() {
            const optionId = parseInt($(this).val());
            
            // Skip if we've already processed this ID
            if (processedIds.has(optionId)) {
                return;
            }
            
            processedIds.add(optionId);
            const $option = $(this);
            const $formCheck = $option.closest('.form-check');
            
            if (compatibleIds.includes(optionId)) {
                // This option is compatible
                $formCheck.removeClass('unsupported-option');
                console.log(`Marked as compatible: ${selector} id=${optionId}`);
            } else {
                // This option is not compatible with selected categories
                $option.prop('checked', false); // Uncheck it
                $formCheck.addClass('unsupported-option'); // Mark as unsupported
                console.log(`Marked as unsupported: ${selector} id=${optionId}`);
            }
        });
    }
    
    // Reset all options to default state
    function resetAllOptions() {
        // Remove unsupported-option class
        $('.add-country, .add-purpose, .add-feature').closest('.form-check').removeClass('unsupported-option');
        
        // Reset all styling
        $('#addSiteForm .form-check').css({
            'opacity': '1',
            'text-decoration': 'none',
            'font-weight': 'normal',
            'display': '',
            'visibility': 'visible'
        });
        
        // Remove any filtering info boxes
        $('.filtering-info-box').remove();
    }
    
    // Update compatibility notes with counts
    function updateCompatibilityNotes(response) {
        const countryCount = response.countries ? response.countries.length : 0;
        const purposeCount = response.purposes ? response.purposes.length : 0;
        const featureCount = response.features ? response.features.length : 0;
        
        $('#countryCompatibilityNoteAdd').html(
            `<i class="fas fa-info-circle me-1"></i> Showing ${countryCount} compatible countries`
        ).removeClass('d-none');
        
        $('#purposeCompatibilityNoteAdd').html(
            `<i class="fas fa-info-circle me-1"></i> Showing ${purposeCount} compatible work purposes`
        ).removeClass('d-none');
        
        $('#featureCompatibilityNoteAdd').html(
            `<i class="fas fa-info-circle me-1"></i> Showing ${featureCount} compatible features`
        ).removeClass('d-none');
    }
    
    // Initialize form when the modal is shown
    $('#AddSiteModal').on('shown.bs.modal', function() {
        // Reset form state
        resetAllOptions();
        $('.add-category').prop('checked', false);
        $('.add-country, .add-purpose, .add-feature').prop('checked', false);
        $('#hideIncompatibleOptions').prop('checked', true); // Default to checked
        $('#selectedCategoriesBadgesAddForm').empty();
        
        // Hide compatibility notes
        $('#purposeCompatibilityNoteAdd, #countryCompatibilityNoteAdd, #featureCompatibilityNoteAdd').addClass('d-none');
        
        // Remove hide-incompatible class
        $('#addSiteForm .compatibility-container').removeClass('hide-incompatible');
        
        console.log('Add Site modal shown - form initialized');
    });
}); 
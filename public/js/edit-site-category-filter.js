/**
 * Edit Site Form Category Filtering
 * 
 * This script improves the category-based filtering of options in the Edit Site form
 */

$(document).ready(function() {
    console.log('Edit Site Category Filter JS loaded');
    
    // Run initial check for pre-selected categories
    setTimeout(function() {
        console.log('Checking for pre-selected categories in Edit form...');
        const selectedCategories = $('.edit-category:checked');
        console.log('Found ' + selectedCategories.length + ' pre-selected categories in Edit form');
        
        if (selectedCategories.length > 0) {
            console.log('Applying initial filtering for pre-selected categories in Edit form');
            updateSelectedCategoriesBadges();
            filterCompatibleOptions();
            // Apply hiding incompatible options on initialization since checkbox is checked by default
            toggleIncompatibleOptions($('#hideIncompatibleOptionsEdit').is(':checked'));
        }
    }, 800);
    
    // When a category is checked/unchecked in the edit form - use both direct binding and delegation
    $('.edit-category').on('change', function() {
        console.log('Category selection changed in Edit form (direct binding)');
        updateSelectedCategoriesBadges();
        filterCompatibleOptions();
    });
    
    // Additional delegation binding for dynamically added elements
    $(document).on('change', '.edit-category', function() {
        console.log('Category selection changed in Edit form (delegation)');
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
        console.log('Hide incompatible options:', hide);
        
        // Find all unsupported options for logging
        const unsupportedCount = $('#editSiteForm .form-check.unsupported-option').length;
        const supportedCount = $('#editSiteForm .form-check').not('.unsupported-option').length;
        console.log(`Edit form has ${unsupportedCount} unsupported options and ${supportedCount} supported options`);
        
        if (hide) {
            // Add class to containers
            $containers.addClass('hide-incompatible');
            console.log('Added hide-incompatible class to containers');
            
            // Also apply direct CSS to all unsupported options for redundancy
            $('#editSiteForm .form-check.unsupported-option').css({
                'display': 'none',
                'visibility': 'hidden'
            });
            
            // Make sure supported options are visible
            $('#editSiteForm .form-check').not('.unsupported-option').css({
                'display': '',
                'visibility': 'visible',
                'opacity': '1'
            });
            
            console.log('Applied direct CSS to hide unsupported options');
        } else {
            // Remove class from containers
            $containers.removeClass('hide-incompatible');
            console.log('Removed hide-incompatible class from containers');
            
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
            
            console.log('Applied direct CSS to show but dim unsupported options');
        }
        
        // Debug counts of visible and hidden options
        setTimeout(function() {
            const visibleCount = $('#editSiteForm .form-check:visible').length;
            const hiddenCount = $('#editSiteForm .form-check:hidden').length;
            console.log('After toggle - Visible options in Edit form:', visibleCount);
            console.log('After toggle - Hidden options in Edit form:', hiddenCount);
        }, 100);
        
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
                
                // Make sure compatible options are visible
                $('#editSiteForm .form-check:not(.unsupported-option)').css({
                    'opacity': '1',
                    'font-weight': 'normal'
                });
                
                // Check if hide incompatible options is checked
                const hideIncompatible = $('#hideIncompatibleOptionsEdit').is(':checked');
                console.log('Hide incompatible options is checked in Edit form:', hideIncompatible);
                
                // Apply hiding if needed
                if (hideIncompatible) {
                    toggleIncompatibleOptions(true);
                } else {
                    // Just style the unsupported options
                    $('#editSiteForm .form-check.unsupported-option').css({
                        'opacity': '0.5',
                        'text-decoration': 'line-through'
                    });
                }
                
                // Add blue info box to show filtering is active
                $('#editSiteForm .compatibility-container').each(function() {
                    const type = $(this).find('input').first().attr('class').split(' ')[1];
                    const compatibleCount = $(this).find('.form-check:not(.unsupported-option)').length;
                    const totalCount = $(this).find('.form-check').length;
                    
                    if (!$(this).prev('.filtering-info-box').length && type) {
                        const typeName = type.replace('edit-', '');
                        $(this).before(
                            `<div class="filtering-info-box alert alert-info py-2 mb-2">
                                Filtering options: Showing ${compatibleCount} compatible ${typeName}s out of ${totalCount}
                            </div>`
                        );
                    }
                });
                
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
        console.log(`Number of supported ${selector} options in Edit form:`, $(selector).closest('.form-check:not(.unsupported-option)').length);
    }
    
    // Reset all options to default state
    function resetAllOptions() {
        // Remove unsupported-option class
        $('.edit-country, .edit-purpose, .edit-feature').closest('.form-check').removeClass('unsupported-option');
        
        // Reset all styling
        $('#editSiteForm .form-check').css({
            'opacity': '1',
            'text-decoration': 'none',
            'font-weight': 'normal',
            'display': '',
            'visibility': 'visible'
        });
        
        // Remove any filtering info boxes
        $('#editSiteForm .filtering-info-box').remove();
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
        
        // Reset filtering info boxes
        $('#editSiteForm .filtering-info-box').remove();
        
        // If categories are already selected, filter options
        if ($('.edit-category:checked').length > 0) {
            setTimeout(function() {
                updateSelectedCategoriesBadges();
                filterCompatibleOptions();
                // Apply hiding incompatible options on initialization since checkbox is checked by default
                toggleIncompatibleOptions(true);
            }, 500);
        } else {
            // Make sure all options are visible if no categories selected
            resetAllOptions();
        }
    });
}); 
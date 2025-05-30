/**
 * Site Filtering JavaScript
 * Handles the filtering of sites based on selected criteria
 */

// Global function to fetch filtered sites via AJAX
function fetchFilteredSites(categories, isGlobal, countries, purposes, minRating, sortByRating) {
    console.log('Fetching filtered sites with params:', {
        categories: categories,
        isGlobal: isGlobal,
        countries: countries,
        purposes: purposes,
        minRating: minRating,
        sortByRating: sortByRating
    });
    
    // Show loading spinner
    $('#loading-spinner').show();
    
    // Hide existing results and pagination
    $('tr.default-site-row').addClass('d-none');
    $('.pagination-container').addClass('d-none');
    $('#no-results').addClass('d-none');
    $('tbody tr:not(.default-site-row):not(#loading-spinner):not(#no-results)').remove();
    
    $.ajax({
        url: '/sites/filter',
        type: 'GET',
        data: {
            categories: categories,
            is_global: isGlobal ? 1 : 0,
            countries: countries,
            purposes: purposes,
            min_rating: minRating,
            sort_by_rating: sortByRating ? 1 : 0
        },
        dataType: 'json',
        success: function(response) {
            console.log('Filter response:', response);
            
            // Hide loading spinner
            $('#loading-spinner').hide();
            
            // Show sites table container
            $('#sitesTableContainer').removeClass('d-none');
            $('#noFiltersMessage').addClass('d-none');
            
            if (response.success && response.sites && response.sites.length > 0) {
                // Save rating settings to global variable if provided
                if (response.rating_settings) {
                    window.ratingSettings = {
                        scale: response.rating_settings.scale || 10,
                        thresholdHigh: response.rating_settings.thresholdHigh || 7,
                        thresholdMedium: response.rating_settings.thresholdMedium || 4,
                        decimalPlaces: response.rating_settings.decimalPlaces || 1
                    };
                    console.log('Received rating settings:', window.ratingSettings);
                }
                
                // Show Excel button if results are found
                $('#copyToExcel').removeClass('d-none');
                
                // Clear any previous filtered rows
                $('tbody tr.filtered-row').remove();
                
                // Append filtered sites to table
                response.sites.forEach(function(site) {
                    // Use dynamic rating thresholds if available
                    const ratingScale = window.ratingSettings?.scale || 10;
                    const thresholdHigh = window.ratingSettings?.thresholdHigh || 7;
                    const thresholdMedium = window.ratingSettings?.thresholdMedium || 4;
                    const decimalPlaces = window.ratingSettings?.decimalPlaces || 1;
                    
                    const ratingClass = site.rating >= thresholdHigh ? 'text-success fw-bold' : 
                                       (site.rating >= thresholdMedium ? 'text-warning' : 'text-danger');
                    
                    const videoButton = site.video_link ? 
                        `<button type="button" class="btn btn-sm btn-outline-primary play-video-btn" data-video-url="${site.video_link}">
                            <i class="fas fa-play-circle"></i> Play
                         </button>` : 
                        `<span class="text-muted">No video</span>`;
                    
                    const editButtons = $('#canManageSites').val() === '1' ?
                        `<div class="btn-group" role="group">
                            <button type="button" value="${site.id}" class="btn btn-primary edit-btn"><i class='bx bxs-edit'></i></button>
                            <button type="button" value="${site.id}" class="btn btn-danger delete-btn"><i class='bx bxs-trash'></i></button>
                         </div>` :
                        `<button type="button" class="btn btn-secondary" disabled><i class='bx bxs-lock'></i></button>`;
                    
                    const row = `<tr class="filtered-row">
                        <td>${site.url}</td>
                        <td>${site.complete_url || 'N/A'}</td>
                        <td>${site.da || 'N/A'}</td>
                        <td>${videoButton}</td>
                        <td>${site.status}</td>
                        <td>${site.server_status || 'Unknown'}</td>
                        <td class="${ratingClass}">${parseFloat(site.rating).toFixed(decimalPlaces)} / ${site.max_rating || ratingScale}</td>
                        <td>${site.categories_list}</td>
                        <td>${site.countries_list || (site.is_global ? 'Global (All Countries)' : 'N/A')}</td>
                        <td>${editButtons}</td>
                    </tr>`;
                    
                    $('tbody').append(row);
                });
                
                // Re-initialize any event handlers for dynamically added elements
                initDynamicEventHandlers();
            } else {
                // Show no results message
                $('#no-results').removeClass('d-none');
            }
        },
        error: function(xhr, status, error) {
            console.error('Error fetching filtered sites:', error);
            console.error('Status:', status);
            console.error('Response:', xhr.responseText);
            
            // Hide loading spinner
            $('#loading-spinner').hide();
            
            // Show error message
            Swal.fire({
                icon: 'error',
                title: 'Filter Error',
                text: 'Failed to load filtered sites. Please try again.'
            });
        }
    });
}

/**
 * Initialize event handlers for dynamically added elements
 */
function initDynamicEventHandlers() {
    // Play video button handler
    $('.play-video-btn').off('click').on('click', function() {
        const videoUrl = $(this).data('video-url');
        if (videoUrl) {
            $('#videoPlayerModal iframe').attr('src', videoUrl);
            $('#videoPlayerModal').modal('show');
        }
    });
    
    // Note: Edit and Delete button handlers should be handled by event delegation
    // already set up in site-form-handlers.js
}

// Function to update filter badges display
function updateFilterBadges(categories, isGlobal, countries, purposes, minRating) {
    const $container = $('#filterBadges');
    $container.empty();
    
    // Get category names
    if (categories && categories.length > 0) {
        const categoryNames = [];
        categories.forEach(function(categoryId) {
            const $category = $('#filter_category' + categoryId);
            if ($category.length) {
                categoryNames.push($category.data('category-name'));
            }
        });
        
        if (categoryNames.length > 0) {
            $container.append(`<span class="badge bg-primary me-1">Categories: ${categoryNames.join(', ')}</span>`);
        }
    }
    
    // Show global or country names
    if (isGlobal) {
        $container.append(`<span class="badge bg-success me-1">Global (All Countries)</span>`);
    } else if (countries && countries.length > 0) {
        const countryNames = [];
        countries.forEach(function(countryId) {
            const $country = $('#filter_country' + countryId);
            if ($country.length) {
                countryNames.push($country.data('country-name'));
            }
        });
        
        if (countryNames.length > 0) {
            $container.append(`<span class="badge bg-info me-1">Countries: ${countryNames.join(', ')}</span>`);
        }
    }
    
    // Show purpose names
    if (purposes && purposes.length > 0) {
        const purposeNames = [];
        purposes.forEach(function(purposeId) {
            const $purpose = $('#filter_purpose' + purposeId);
            if ($purpose.length) {
                purposeNames.push($purpose.data('purpose-name'));
            }
        });
        
        if (purposeNames.length > 0) {
            $container.append(`<span class="badge bg-secondary me-1">Purposes: ${purposeNames.join(', ')}</span>`);
        }
    }
    
    // Show minimum rating
    if (minRating > 0) {
        $container.append(`<span class="badge bg-warning text-dark me-1">Min. Rating: ${minRating}</span>`);
    }
}

// Copy to Excel functionality
$(document).ready(function() {
    $('#copyToExcel').on('click', function() {
        // Get table data
        const rows = [];
        const headers = [];
        
        // Get headers
        $('#sitesTableContainer table thead th').each(function() {
            headers.push($(this).text());
        });
        
        rows.push(headers);
        
        // Get visible row data
        $('#sitesTableContainer table tbody tr:visible').each(function() {
            const rowData = [];
            $(this).find('td').each(function(index) {
                // Skip the actions column (last column)
                if (index < headers.length - 1) {
                    rowData.push($(this).text().trim());
                }
            });
            
            if (rowData.length > 0) {
                rows.push(rowData);
            }
        });
        
        // Create CSV content
        let csvContent = '';
        rows.forEach(function(row) {
            csvContent += row.join('\t') + '\n';
        });
        
        // Copy to clipboard
        const textarea = document.createElement('textarea');
        textarea.value = csvContent;
        document.body.appendChild(textarea);
        textarea.select();
        document.execCommand('copy');
        document.body.removeChild(textarea);
        
        // Show success message
        Swal.fire({
            icon: 'success',
            title: 'Copied!',
            text: 'Table data copied to clipboard. You can now paste it into Excel.',
            timer: 2000,
            showConfirmButton: false
        });
    });
}); 
/**
 * Site Ratings JavaScript - Handles calculation of ratings based on compatible features
 */

// Global rating settings - will be populated from server
let ratingSettings = {
    scale: 10,
    thresholdHigh: 7,
    thresholdMedium: 4,
    decimalPlaces: 1
};

// Function to fetch rating settings from the server
function fetchRatingSettings() {
    // If window.ratingSettings is defined, use those values
    if (window.ratingSettings) {
        ratingSettings = window.ratingSettings;
        console.log('Using rating settings from window:', ratingSettings);
        return;
    }
    
    // Otherwise use defaults
    console.log('Using default rating settings:', ratingSettings);
}

// Function to update the rating progress bar and values in the Add form
function updateRating() {
    let totalPoints = 0;
    let maxPoints = 0;
    
    // Only count compatible features for both total and max points
    $('.feature-checkbox').each(function() {
        // Only count features that are compatible (not hidden or disabled)
        if (!$(this).closest('.form-check').hasClass('unsupported-option') && !$(this).prop('disabled')) {
            const points = parseInt($(this).data('points') || 0);
            maxPoints += points;
            
            if ($(this).is(':checked')) {
                totalPoints += points;
            }
        }
    });
    
    // If no compatible features, use default max
    if (maxPoints === 0) {
        maxPoints = parseInt($('#maxRating').text() || 0);
    }
    
    const percentage = (maxPoints > 0) ? (totalPoints / maxPoints) * 100 : 0;
    const normalizedRating = (maxPoints > 0) ? (totalPoints / maxPoints) * ratingSettings.scale : 0;
    
    $('#currentRating').text(totalPoints);
    $('#maxRating').text(maxPoints);
    $('#normalizedRating').text(normalizedRating.toFixed(ratingSettings.decimalPlaces));
    $('#ratingProgress').css('width', percentage + '%').attr('aria-valuenow', percentage).text(Math.round(percentage) + '%');
    
    // Change progress bar color based on rating percentage and thresholds
    const ratingPercentage = (normalizedRating / ratingSettings.scale) * 100;
    const mediumThresholdPercent = (ratingSettings.thresholdMedium / ratingSettings.scale) * 100;
    const highThresholdPercent = (ratingSettings.thresholdHigh / ratingSettings.scale) * 100;
    
    if (ratingPercentage < mediumThresholdPercent) {
        $('#ratingProgress').removeClass('bg-warning bg-success').addClass('bg-danger');
    } else if (ratingPercentage < highThresholdPercent) {
        $('#ratingProgress').removeClass('bg-danger bg-success').addClass('bg-warning');
    } else {
        $('#ratingProgress').removeClass('bg-danger bg-warning').addClass('bg-success');
    }
    
    console.log('Rating updated:', {
        totalPoints: totalPoints,
        maxPoints: maxPoints,
        percentage: percentage.toFixed(1) + '%',
        normalizedRating: normalizedRating.toFixed(ratingSettings.decimalPlaces)
    });
}

// Function to update the rating progress bar and values for edit form
function updateEditRating() {
    let totalPoints = 0;
    let maxPoints = 0;
    
    // Only count compatible features for both total and max points
    $('.edit-feature-checkbox').each(function() {
        // Only count features that are compatible (not hidden or disabled)
        if (!$(this).closest('.form-check').hasClass('unsupported-option') && !$(this).prop('disabled')) {
            const points = parseInt($(this).data('points') || 0);
            maxPoints += points;
            
            if ($(this).is(':checked')) {
                totalPoints += points;
            }
        }
    });
    
    // If no compatible features, use default max
    if (maxPoints === 0) {
        maxPoints = parseInt($('#edit_maxRating').text() || 0);
    }
    
    const percentage = (maxPoints > 0) ? (totalPoints / maxPoints) * 100 : 0;
    const normalizedRating = (maxPoints > 0) ? (totalPoints / maxPoints) * ratingSettings.scale : 0;
    
    $('#edit_currentRating').text(totalPoints);
    $('#edit_maxRating').text(maxPoints);
    $('#edit_normalizedRating').text(normalizedRating.toFixed(ratingSettings.decimalPlaces));
    $('#edit_ratingProgress').css('width', percentage + '%').attr('aria-valuenow', percentage).text(Math.round(percentage) + '%');
    
    // Change progress bar color based on rating percentage and thresholds
    const ratingPercentage = (normalizedRating / ratingSettings.scale) * 100;
    const mediumThresholdPercent = (ratingSettings.thresholdMedium / ratingSettings.scale) * 100;
    const highThresholdPercent = (ratingSettings.thresholdHigh / ratingSettings.scale) * 100;
    
    if (ratingPercentage < mediumThresholdPercent) {
        $('#edit_ratingProgress').removeClass('bg-warning bg-success').addClass('bg-danger');
    } else if (ratingPercentage < highThresholdPercent) {
        $('#edit_ratingProgress').removeClass('bg-danger bg-success').addClass('bg-warning');
    } else {
        $('#edit_ratingProgress').removeClass('bg-danger bg-warning').addClass('bg-success');
    }
    
    console.log('Edit Rating updated:', {
        totalPoints: totalPoints,
        maxPoints: maxPoints,
        percentage: percentage.toFixed(1) + '%',
        normalizedRating: normalizedRating.toFixed(ratingSettings.decimalPlaces)
    });
}

// Initialize event handlers
$(document).ready(function() {
    // Initialize rating settings
    fetchRatingSettings();
    
    // Calculate rating when features are toggled in add form
    $(document).on('change', '.feature-checkbox', function() {
        console.log('Feature checkbox changed in add form');
        updateRating();
    });

    // Calculate rating when features are toggled in edit form
    $(document).on('change', '.edit-feature-checkbox', function() {
        console.log('Feature checkbox changed in edit form');
        updateEditRating();
    });
    
    // Update rating when categories change compatibility
    $(document).on('change', '.add-category', function() {
        console.log('Category changed in add form - updating rating');
        // Wait for the compatibility filtering to complete
        setTimeout(updateRating, 500);
    });
    
    // Update rating when categories change compatibility in edit form
    $(document).on('change', '.edit-category', function() {
        console.log('Category changed in edit form - updating rating');
        // Wait for the compatibility filtering to complete
        setTimeout(updateEditRating, 500);
    });
    
    // Initialize ratings on page load
    setTimeout(function() {
        console.log('Initializing ratings on page load');
        updateRating();
        updateEditRating();
    }, 1000);
}); 
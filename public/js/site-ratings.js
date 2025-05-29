/**
 * Site Ratings JavaScript - Handles calculation of ratings based on compatible features
 */

// Function to update the rating progress bar and values in the Add form
function updateRating() {
    let totalPoints = 0;
    let maxPoints = 0;
    
    // Only count compatible features for both total and max points
    $('.feature-checkbox').each(function() {
        // Only count features that are compatible (not hidden)
        if (!$(this).closest('.form-check').hasClass('unsupported-option')) {
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
    $('#currentRating').text(totalPoints);
    $('#maxRating').text(maxPoints);
    $('#ratingProgress').css('width', percentage + '%').attr('aria-valuenow', percentage).text(Math.round(percentage) + '%');
    
    // Change progress bar color based on rating
    if (percentage < 33) {
        $('#ratingProgress').removeClass('bg-warning bg-success').addClass('bg-danger');
    } else if (percentage < 66) {
        $('#ratingProgress').removeClass('bg-danger bg-success').addClass('bg-warning');
    } else {
        $('#ratingProgress').removeClass('bg-danger bg-warning').addClass('bg-success');
    }
    
    // No need to manipulate submit button here - that should be handled elsewhere
}

// Function to update the rating progress bar and values for edit form
function updateEditRating() {
    let totalPoints = 0;
    let maxPoints = 0;
    
    // Only count compatible features for both total and max points
    $('.edit-feature-checkbox').each(function() {
        // Only count features that are compatible (not hidden)
        if (!$(this).closest('.form-check').hasClass('unsupported-option')) {
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
    $('#edit_currentRating').text(totalPoints);
    $('#edit_maxRating').text(maxPoints);
    $('#edit_ratingProgress').css('width', percentage + '%').attr('aria-valuenow', percentage).text(Math.round(percentage) + '%');
    
    // Change progress bar color based on rating
    if (percentage < 33) {
        $('#edit_ratingProgress').removeClass('bg-warning bg-success').addClass('bg-danger');
    } else if (percentage < 66) {
        $('#edit_ratingProgress').removeClass('bg-danger bg-success').addClass('bg-warning');
    } else {
        $('#edit_ratingProgress').removeClass('bg-danger bg-warning').addClass('bg-success');
    }
    
    // No need to manipulate submit button here - that should be handled elsewhere
}

// Initialize event handlers
$(document).ready(function() {
    // Calculate rating when features are toggled in add form
    $(document).on('change', '.feature-checkbox', function() {
        updateRating();
    });

    // Calculate rating when features are toggled in edit form
    $(document).on('change', '.edit-feature-checkbox', function() {
        updateEditRating();
    });
    
    // Update rating when categories change compatibility
    $(document).on('change', '.add-category', function() {
        // Wait for the compatibility filtering to complete
        setTimeout(updateRating, 500);
    });
    
    // Update rating when categories change compatibility in edit form
    $(document).on('change', '.edit-category', function() {
        // Wait for the compatibility filtering to complete
        setTimeout(updateEditRating, 500);
    });
    
    // Initialize ratings on page load
    updateRating();
    updateEditRating();
}); 
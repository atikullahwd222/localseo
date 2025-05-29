/**
 * Domain Status Check Functionality
 * 
 * This script handles checking domain reachability for site forms.
 */

$(document).ready(function() {
    console.log('Domain checker JS loaded');
    
    // Store route URLs globally
    window.routeUrls = window.routeUrls || {};
    
    // Add Site form domain status check - use direct binding
    $('#checkDomainBtn').on('click', function() {
        console.log('Check domain button clicked (Add form)');
        checkDomain('add');
    });
    
    // Edit form domain status check - use direct binding
    $('#editCheckDomainBtn').on('click', function() {
        console.log('Check domain button clicked (Edit form)');
        checkDomain('edit');
    });
    
    /**
     * Check domain reachability
     * @param {string} formType - 'add' or 'edit'
     */
    function checkDomain(formType) {
        const prefix = formType === 'edit' ? 'edit_' : '';
        const domain = $(`#${prefix}url`).val().trim();
        const $submitBtn = formType === 'edit' ? $('.edit_btn') : $('#saveSiteBtn');
        const $feedback = $(`#${prefix}serverStatusFeedback`);
        const $ignoreContainer = $(`#${prefix}ignoreServerStatusContainer`);
        const $message = $(`#${prefix}serverStatusMessage`);
        
        console.log(`Checking domain ${domain} in ${formType} form`);
        
        if (!domain) {
            $feedback.removeClass('d-none');
            $feedback.find('.alert').removeClass('alert-success alert-danger').addClass('alert-warning');
            $message.html('<i class="fas fa-exclamation-triangle me-2"></i> Please enter a domain first');
            return;
        }
        
        // First validate the format
        if (!validateDomain(domain)) {
            $feedback.removeClass('d-none');
            $feedback.find('.alert').removeClass('alert-success alert-info').addClass('alert-danger');
            $message.html('<i class="fas fa-times-circle me-2"></i> Invalid domain format. Please enter a valid domain name.');
            $submitBtn.prop('disabled', true);
            return;
        }
        
        // Show checking message
        $feedback.removeClass('d-none');
        $feedback.find('.alert').removeClass('alert-success alert-danger alert-warning').addClass('alert-info');
        $message.html('<i class="fas fa-spinner fa-spin me-2"></i> Checking domain reachability...');
        
        // Check domain reachability with hardcoded URL as fallback
        const checkReachabilityUrl = routeUrls.checkReachability || '/api/check-domain-reachability';
        console.log('Using URL:', checkReachabilityUrl);
        
        // Check domain reachability
        $.ajax({
            url: checkReachabilityUrl,
            type: 'GET',
            data: { domain: domain },
            success: function(response) {
                console.log('Domain check response:', response);
                
                if (response.success) {
                    if (response.is_reachable) {
                        // Domain is reachable
                        $feedback.find('.alert').removeClass('alert-info alert-danger alert-warning').addClass('alert-success');
                        $message.html('<i class="fas fa-check-circle me-2"></i> Domain is reachable. Server status: <strong>Online</strong>');
                        $submitBtn.prop('disabled', false);
                        $ignoreContainer.addClass('d-none');
                    } else {
                        // Domain is not reachable
                        $feedback.find('.alert').removeClass('alert-info alert-success alert-warning').addClass('alert-danger');
                        $message.html('<i class="fas fa-exclamation-circle me-2"></i> Domain is not reachable. Server status: <strong>Offline</strong>');
                        $submitBtn.prop('disabled', true);
                        $ignoreContainer.removeClass('d-none');
                    }
                } else {
                    // Error in response
                    $feedback.find('.alert').removeClass('alert-info alert-success').addClass('alert-warning');
                    $message.html('<i class="fas fa-exclamation-triangle me-2"></i> ' + (response.message || 'Error checking domain'));
                    $submitBtn.prop('disabled', true);
                    $ignoreContainer.removeClass('d-none');
                }
            },
            error: function(xhr, status, error) {
                // AJAX error
                console.error('Domain check AJAX error:', status, error);
                $feedback.find('.alert').removeClass('alert-info alert-success').addClass('alert-warning');
                $message.html('<i class="fas fa-exclamation-triangle me-2"></i> Error checking domain. Server may be unavailable.');
                $submitBtn.prop('disabled', true);
                $ignoreContainer.removeClass('d-none');
            }
        });
    }
    
    // Toggle submit button based on ignore checkbox in add form
    $(document).on('change', '#ignoreServerStatus', function() {
        const $submitBtn = $('#saveSiteBtn');
        if ($(this).is(':checked')) {
            $submitBtn.prop('disabled', false);
            console.log('Add form: Enabling submit button due to ignore checkbox');
        } else {
            $submitBtn.prop('disabled', true);
            console.log('Add form: Disabling submit button due to ignore checkbox');
        }
    });
    
    // Toggle submit button based on ignore checkbox in edit form
    $(document).on('change', '#editIgnoreServerStatus', function() {
        const $submitBtn = $('.edit_btn');
        if ($(this).is(':checked')) {
            $submitBtn.prop('disabled', false);
            console.log('Edit form: Enabling submit button due to ignore checkbox');
        } else {
            $submitBtn.prop('disabled', true);
            console.log('Edit form: Disabling submit button due to ignore checkbox');
        }
    });
    
    // Domain validation function
    function validateDomain(domain) {
        // Allow simple domain validation with optional subdomain
        // Simple regex for domain validation - more permissive
        const domainRegex = /^([a-zA-Z0-9]([a-zA-Z0-9\-]{0,61}[a-zA-Z0-9])?\.)+[a-zA-Z]{2,}$/;
        return domainRegex.test(domain);
    }
    
    // Auto-populate complete URL from domain in add form
    $(document).on('blur', '#url', function() {
        const domain = $(this).val().trim();
        const currentCompleteUrl = $('#complete_url').val().trim();
        
        if (domain && !currentCompleteUrl) {
            // Only auto-populate if complete URL is empty
            $('#complete_url').val('https://' + domain);
            console.log('Auto-populated complete URL in add form:', 'https://' + domain);
        }
    });
    
    // Auto-populate complete URL from domain in edit form
    $(document).on('blur', '#edit_url', function() {
        const domain = $(this).val().trim();
        const currentCompleteUrl = $('#edit_complete_url').val().trim();
        
        if (domain && !currentCompleteUrl) {
            // Only auto-populate if complete URL is empty
            $('#edit_complete_url').val('https://' + domain);
            console.log('Auto-populated complete URL in edit form:', 'https://' + domain);
        }
    });
}); 
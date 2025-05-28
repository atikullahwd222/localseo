@section('title', 'Register')
<x-guest-layout>
    <h4 class="mb-2">Adventure starts here 🚀</h4>
    <p class="mb-4">Make your app management easy and fun!</p>

    <div id="register-alert" class="alert alert-danger d-none"></div>

    <form id="registerForm" method="POST" action="{{ route('register') }}" class="mb-3">
        @csrf

        <!-- Name -->
        <div class="mb-3">
            <label for="name" class="form-label">Username</label>
            <input type="text" class="form-control @error('name') is-invalid @enderror" 
                id="name" name="name" value="{{ old('name') }}" 
                placeholder="Enter your username" autofocus />
            <div class="invalid-feedback" id="name_error"></div>
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- Email Address -->
        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="text" class="form-control @error('email') is-invalid @enderror" 
                id="email" name="email" value="{{ old('email') }}" 
                placeholder="Enter your email" />
            <div class="invalid-feedback" id="email_error"></div>
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mb-3 form-password-toggle">
            <label class="form-label" for="password">Password</label>
            <div class="input-group input-group-merge">
                <input type="password" id="password" 
                    class="form-control @error('password') is-invalid @enderror" 
                    name="password" 
                    placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;" 
                    aria-describedby="password" />
                <span class="input-group-text cursor-pointer"><i class="bx bx-hide"></i></span>
            </div>
            <div class="invalid-feedback" id="password_error"></div>
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div class="mb-3 form-password-toggle">
            <label class="form-label" for="password_confirmation">Confirm Password</label>
            <div class="input-group input-group-merge">
                <input type="password" id="password_confirmation" 
                    class="form-control" 
                    name="password_confirmation" 
                    placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;" />
                <span class="input-group-text cursor-pointer"><i class="bx bx-hide"></i></span>
            </div>
            <div class="invalid-feedback" id="password_confirmation_error"></div>
        </div>

        <div class="mb-3">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="terms-conditions" name="terms" />
                <label class="form-check-label" for="terms-conditions">
                    I agree to the privacy policy & terms
                </label>
            </div>
        </div>

        <button class="btn btn-primary d-grid w-100" type="submit" id="register-btn" style="height: 38px;">
            <div class="d-flex align-items-center justify-content-center">
                <span id="register-btn-text">Sign up</span>
                <div class="spinner-border spinner-border-sm ms-2 d-none" role="status" id="register-spinner"></div>
            </div>
        </button>
    </form>

    <p class="text-center">
        <span>Already have an account?</span>
        <a href="{{ route('login') }}">
            <span>Sign in instead</span>
        </a>
    </p>
    
    <!-- Include SweetAlert -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Make sure spinner is hidden on page load
        document.getElementById('register-spinner').classList.add('d-none');
        
        $('#registerForm').on('submit', function(e) {
            e.preventDefault();
            
            // Reset errors
            $('.is-invalid').removeClass('is-invalid');
            $('.invalid-feedback').text('');
            $('#register-alert').addClass('d-none').text('');
            
            // Check terms
            if (!$('#terms-conditions').is(':checked')) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Terms Required',
                    text: 'You must agree to the terms and conditions.',
                    confirmButtonText: 'OK'
                });
                return false;
            }
            
            // Show spinner
            $('#register-btn-text').text('Creating account...');
            $('#register-spinner').removeClass('d-none');
            $('#register-btn').prop('disabled', true);
            
            // Submit form via AJAX
            $.ajax({
                url: $(this).attr('action'),
                type: 'POST',
                data: $(this).serialize(),
                dataType: 'json',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                success: function(response) {
                    if (response.success) {
                        // Show success message
                        Swal.fire({
                            icon: 'success',
                            title: 'Registration Successful!',
                            text: response.message || 'Your account has been created successfully!',
                            showConfirmButton: true,
                            confirmButtonText: 'Go to Login'
                        }).then(function() {
                            // Redirect to login page
                            window.location.href = response.redirect;
                        });
                    }
                },
                error: function(xhr) {
                    // Hide spinner
                    $('#register-btn-text').text('Sign up');
                    $('#register-spinner').addClass('d-none');
                    $('#register-btn').prop('disabled', false);
                    
                    if (xhr.status === 422) {
                        var errors = xhr.responseJSON.errors;
                        
                        // Display error messages
                        if (errors) {
                            const firstField = Object.keys(errors)[0];
                            const firstError = errors[firstField][0];
                            
                            Swal.fire({
                                icon: 'error',
                                title: 'Registration Failed',
                                text: firstError,
                                confirmButtonText: 'OK'
                            });
                            
                            $.each(errors, function(field, messages) {
                                $('#' + field).addClass('is-invalid');
                                $('#' + field + '_error').text(messages[0]);
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'An error occurred. Please try again.',
                                confirmButtonText: 'OK'
                            });
                        }
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Server Error',
                            text: 'Something went wrong. Please try again later.',
                            confirmButtonText: 'OK'
                        });
                    }
                }
            });
        });
    });
    </script>
    
    <style>
    /* Ensure the spinner appears correctly */
    .spinner-border {
        border-width: 0.2em;
    }
    </style>
</x-guest-layout>

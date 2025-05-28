@section('title', 'Login')
<x-guest-layout>
    <h4 class="mb-2">Welcome to {{ config('app.name') }}! 👋</h4>
    <p class="mb-4">Please sign-in to your account</p>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <!-- Error Message -->
    @if (session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    <div id="login-alert" class="alert alert-danger d-none"></div>

    <form id="loginForm" method="POST" action="{{ route('login') }}" class="mb-3">
        @csrf

        <!-- Email Address -->
        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="text" class="form-control @error('email') is-invalid @enderror" id="email" 
                name="email" value="{{ old('email') }}" placeholder="Enter your email" autofocus />
            <div class="invalid-feedback" id="email_error"></div>
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mb-3 form-password-toggle">
            <div class="d-flex justify-content-between">
                <label class="form-label" for="password">Password</label>
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}">
                        <small>Forgot Password?</small>
                    </a>
                @endif
            </div>
            <div class="input-group input-group-merge">
                <input type="password" id="password" class="form-control @error('password') is-invalid @enderror" 
                    name="password" placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;" 
                    aria-describedby="password" />
                <span class="input-group-text cursor-pointer"><i class="bx bx-hide"></i></span>
            </div>
            <div class="invalid-feedback" id="password_error"></div>
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="mb-3">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="remember_me" name="remember">
                <label class="form-check-label" for="remember_me">
                    Remember Me
                </label>
            </div>
        </div>

        <div class="mb-3">
            <button class="btn btn-primary d-grid w-100" type="submit" id="login-btn" style="height: 38px;">
                <div class="d-flex align-items-center justify-content-center">
                    <span id="login-btn-text">Sign in</span>
                    <div class="spinner-border spinner-border-sm ms-2 d-none" role="status" id="login-spinner"></div>
                </div>
            </button>
        </div>
    </form>

    <p class="text-center">
        <span>New on our platform?</span>
        <a href="{{ route('register') }}">
            <span>Create an account</span>
        </a>
    </p>

    <!-- Include SweetAlert -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Make sure spinner is hidden on page load
        document.getElementById('login-spinner').classList.add('d-none');
        
        // Process session status messages
        @if(session('status'))
        Swal.fire({
            icon: 'success',
            title: 'Success',
            text: "{{ session('status') }}",
            timer: 3000,
            showConfirmButton: false
        });
        @endif
        
        // Process session error messages
        @if(session('error'))
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: "{{ session('error') }}",
            confirmButtonText: 'OK'
        });
        @endif

        $('#loginForm').on('submit', function(e) {
            e.preventDefault();
            
            // Reset errors
            $('.is-invalid').removeClass('is-invalid');
            $('.invalid-feedback').text('');
            $('#login-alert').addClass('d-none').text('');
            
            // Show spinner
            $('#login-btn-text').text('Signing in...');
            $('#login-spinner').removeClass('d-none');
            $('#login-btn').prop('disabled', true);
            
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
                            title: 'Success!',
                            text: response.message || 'Login successful!',
                            timer: 1500,
                            showConfirmButton: false
                        }).then(function() {
                            // Redirect to dashboard
                            window.location.href = response.redirect;
                        });
                    }
                },
                error: function(xhr) {
                    // Hide spinner
                    $('#login-btn-text').text('Sign in');
                    $('#login-spinner').addClass('d-none');
                    $('#login-btn').prop('disabled', false);
                    
                    if (xhr.status === 422) {
                        var errors = xhr.responseJSON.errors;
                        
                        if (errors) {
                            // Check for "pending deletion" error specifically
                            if (errors.email && errors.email[0].includes('pending deletion')) {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Account Pending Deletion',
                                    html: '<div class="text-center">' +
                                          '<i class="bx bx-error-circle text-danger mb-3" style="font-size: 5rem;"></i>' +
                                          '<p class="text-danger fw-bold fs-5">' + errors.email[0] + '</p>' +
                                          '<p class="mt-2">Please contact the administrator for assistance.</p>' +
                                          '</div>',
                                    showConfirmButton: true,
                                    confirmButtonText: 'OK',
                                    customClass: {
                                        popup: 'border-danger'
                                    }
                                });
                                return;
                            }
                            // Check for other account status errors
                            else if (errors.email && errors.email[0].includes('inactive')) {
                                Swal.fire({
                                    icon: 'warning',
                                    title: 'Inactive Account',
                                    text: errors.email[0],
                                    confirmButtonText: 'OK'
                                });
                                return;
                            }
                            // For all other errors show the first one with SweetAlert
                            else {
                                const firstField = Object.keys(errors)[0];
                                const firstError = errors[firstField][0];
                                
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Login Failed',
                                    text: firstError,
                                    confirmButtonText: 'OK'
                                });
                                
                                // Also display errors in form fields
                                $.each(errors, function(field, messages) {
                                    $('#' + field).addClass('is-invalid');
                                    $('#' + field + '_error').text(messages[0]);
                                });
                            }
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
    .border-danger {
        border-top: 5px solid #dc3545 !important;
    }
    
    /* Ensure the spinner appears correctly */
    .spinner-border {
        border-width: 0.2em;
    }
    </style>
</x-guest-layout>

<x-app-layout>
@section('title', 'Security Settings')
@section('content')
    <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Account Settings /</span> Security</h4>

    <div class="row">
        <div class="col-md-12">
            <ul class="nav nav-pills flex-column flex-md-row mb-3">
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('profile.edit') }}">
                        <i class="bx bx-user me-1"></i> Account
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="{{ route('settings') }}">
                        <i class="bx bx-lock-alt me-1"></i> Security
                    </a>
                </li>
            </ul>

            <!-- Change Password -->
            <div class="card mb-4">
                <h5 class="card-header">Change Password</h5>
                <div class="card-body">
                    <form id="passwordUpdateForm" method="post" action="{{ route('password.update') }}">
                        @csrf
                        @method('put')
                        <div class="row">
                            <div class="mb-3 col-md-6">
                                <label for="current_password" class="form-label">Current Password</label>
                                <input class="form-control" type="password" id="current_password" name="current_password" placeholder="••••••"/>
                                <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-2" />
                                <div class="invalid-feedback" id="current_password_error"></div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="mb-3 col-md-6">
                                <label for="password" class="form-label">New Password</label>
                                <input class="form-control" type="password" id="password" name="password" placeholder="••••••"/>
                                <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-2" />
                                <div class="invalid-feedback" id="password_error"></div>
                            </div>

                            <div class="mb-3 col-md-6">
                                <label for="password_confirmation" class="form-label">Confirm New Password</label>
                                <input class="form-control" type="password" id="password_confirmation" name="password_confirmation" placeholder="••••••"/>
                                <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-2" />
                                <div class="invalid-feedback" id="password_confirmation_error"></div>
                            </div>
                        </div>
                        <div class="mt-2">
                            <button type="submit" class="btn btn-primary me-2" id="passwordUpdateBtn">
                                <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true" id="passwordSpinner"></span>
                                <span id="passwordBtnText">Save changes</span>
                            </button>
                            <button type="reset" class="btn btn-outline-secondary">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
            <!-- /Change Password -->

            <!-- Delete Account -->
            <div class="card">
                <h5 class="card-header">Delete Account</h5>
                <div class="card-body">
                    <div class="mb-3 col-12">
                        <div class="alert alert-warning">
                            <h6 class="alert-heading fw-bold mb-1">Are you sure you want to delete your account?</h6>
                            <p class="mb-0">Once you request account deletion, your account will be deactivated and set for review by an administrator.</p>
                        </div>
                    </div>
                    <form id="formAccountDeactivation" method="post" action="{{ route('profile.destroy') }}">
                        @csrf
                        @method('delete')
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" name="accountActivation" id="accountActivation" required/>
                            <label class="form-check-label" for="accountActivation">I confirm my account deactivation</label>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Enter Your Password</label>
                            <input class="form-control" type="password" id="delete_password" name="password" placeholder="••••••" required/>
                            <div class="invalid-feedback" id="delete_password_error"></div>
                        </div>
                        <button type="submit" class="btn btn-danger" id="deactivateAccountBtn">
                            <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true" id="deleteSpinner"></span>
                            <span id="deleteBtnText">Deactivate Account</span>
                        </button>
                    </form>
                </div>
            </div>
            <!-- /Delete Account -->
        </div>
    </div>
@endsection

@push('scripts')
<!-- Include SweetAlert -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Password Update Form
    const passwordForm = document.getElementById('passwordUpdateForm');
    if (passwordForm) {
        passwordForm.addEventListener('submit', function(e) {
            e.preventDefault();

            // Show spinner
            const spinner = document.getElementById('passwordSpinner');
            const btnText = document.getElementById('passwordBtnText');
            const submitBtn = document.getElementById('passwordUpdateBtn');

            spinner.classList.remove('d-none');
            btnText.textContent = 'Saving...';
            submitBtn.disabled = true;

            // Reset form errors
            resetFormErrors();

            const formData = new FormData(passwordForm);

            fetch(passwordForm.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                credentials: 'same-origin'
            })
            .then(response => {
                if (!response.ok) {
                    return response.json().then(data => {
                        throw data;
                    });
                }
                return response.json();
            })
            .then(data => {
                // Show success message using SweetAlert
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: data.message || 'Password updated successfully',
                    timer: 3000,
                    showConfirmButton: false
                });

                // Reset form
                passwordForm.reset();
            })
            .catch(error => {
                console.error('Error:', error);

                // Handle validation errors
                if (error.errors) {
                    Object.keys(error.errors).forEach(field => {
                        const errorElement = document.getElementById(`${field}_error`);
                        if (errorElement) {
                            errorElement.textContent = error.errors[field][0];
                            document.getElementById(field).classList.add('is-invalid');
                        }
                    });
                } else {
                    // Show error message using SweetAlert
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: 'An error occurred while updating your password. Please try again.',
                    });
                }
            })
            .finally(() => {
                // Hide spinner
                spinner.classList.add('d-none');
                btnText.textContent = 'Save changes';
                submitBtn.disabled = false;
            });
        });
    }

    // Account Deactivation Form
    const deleteForm = document.getElementById('formAccountDeactivation');
    if (deleteForm) {
        deleteForm.addEventListener('submit', function(e) {
            e.preventDefault();

            // First confirm with SweetAlert
            Swal.fire({
                title: 'Are you sure?',
                text: "Your account will be deactivated and set for deletion review!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, deactivate it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    submitDeactivationForm();
                }
            });
        });
    }

    function submitDeactivationForm() {
        // Show spinner
        const spinner = document.getElementById('deleteSpinner');
        const btnText = document.getElementById('deleteBtnText');
        const submitBtn = document.getElementById('deactivateAccountBtn');

        spinner.classList.remove('d-none');
        btnText.textContent = 'Processing...';
        submitBtn.disabled = true;

        // Reset form errors
        document.getElementById('delete_password').classList.remove('is-invalid');
        document.getElementById('delete_password_error').textContent = '';

        const formData = new FormData(deleteForm);

        fetch(deleteForm.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            credentials: 'same-origin'
        })
        .then(response => {
            if (!response.ok) {
                return response.json().then(data => {
                    throw data;
                });
            }
            return response.json();
        })
        .then(data => {
            // Show success message using SweetAlert
            Swal.fire({
                icon: 'success',
                title: 'Account Deactivation Requested',
                text: data.message || 'Your account has been marked for deletion.',
                confirmButtonText: 'OK'
            }).then((result) => {
                // Redirect to login page
                window.location.href = "{{ route('login') }}";
            });
        })
        .catch(error => {
            console.error('Error:', error);

            // Handle validation errors
            if (error.errors) {
                if (error.errors.password) {
                    document.getElementById('delete_password').classList.add('is-invalid');
                    document.getElementById('delete_password_error').textContent = error.errors.password[0];
                }
            } else {
                // Show error message using SweetAlert
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: 'An error occurred while processing your request. Please try again.',
                });
            }

            // Hide spinner
            spinner.classList.add('d-none');
            btnText.textContent = 'Deactivate Account';
            submitBtn.disabled = false;
        });
    }

    function resetFormErrors() {
        // Reset password form errors
        const fields = ['current_password', 'password', 'password_confirmation'];
        fields.forEach(field => {
            const element = document.getElementById(field);
            if (element) {
                element.classList.remove('is-invalid');
                const errorElement = document.getElementById(`${field}_error`);
                if (errorElement) {
                    errorElement.textContent = '';
                }
            }
        });
    }
});
</script>
@endpush
</x-app-layout>

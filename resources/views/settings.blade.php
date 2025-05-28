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
                    <form method="post" action="{{ route('password.update') }}">
                        @csrf
                        @method('put')
                        <div class="row">
                            <div class="mb-3 col-md-6">
                                <label for="current_password" class="form-label">Current Password</label>
                                <input class="form-control" type="password" id="current_password" name="current_password" placeholder="••••••"/>
                                <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-2" />
                            </div>
                        </div>
                        <div class="row">
                            <div class="mb-3 col-md-6">
                                <label for="password" class="form-label">New Password</label>
                                <input class="form-control" type="password" id="password" name="password" placeholder="••••••"/>
                                <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-2" />
                            </div>

                            <div class="mb-3 col-md-6">
                                <label for="password_confirmation" class="form-label">Confirm New Password</label>
                                <input class="form-control" type="password" id="password_confirmation" name="password_confirmation" placeholder="••••••"/>
                                <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-2" />
                            </div>
                        </div>
                        <div class="mt-2">
                            <button type="submit" class="btn btn-primary me-2">Save changes</button>
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
                    <div class="mb-3 col-12 mb-0">
                        <div class="alert alert-warning">
                            <h6 class="alert-heading fw-bold mb-1">Are you sure you want to delete your account?</h6>
                            <p class="mb-0">Once you delete your account, there is no going back. Please be certain.</p>
                        </div>
                    </div>
                    <form method="post" action="{{ route('profile.destroy') }}" id="formAccountDeactivation">
                        @csrf
                        @method('delete')
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" name="accountActivation" id="accountActivation" required/>
                            <label class="form-check-label" for="accountActivation">I confirm my account deactivation</label>
                        </div>
                        <button type="submit" class="btn btn-danger deactivate-account">Deactivate Account</button>
                    </form>
                </div>
            </div>
            <!-- /Delete Account -->
        </div>
    </div>
@endsection
</x-app-layout> 
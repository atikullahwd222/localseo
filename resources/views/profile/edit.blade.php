<x-app-layout>
@section('title', 'Account Settings')
@section('content')
    <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Account Settings /</span> Account</h4>

    <div class="row">
        <div class="col-md-12">
            <ul class="nav nav-pills flex-column flex-md-row mb-3">
                <li class="nav-item">
                    <a class="nav-link active" href="{{ route('profile.edit') }}">
                        <i class="bx bx-user me-1"></i> Account
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('settings') }}">
                        <i class="bx bx-lock-alt me-1"></i> Security
                    </a>
                </li>
            </ul>

            <div class="card mb-4">
                <h5 class="card-header">Profile Details</h5>
                <!-- Account -->
                <div class="card-body">
                    <form method="post" action="{{ route('profile.photo.update') }}" enctype="multipart/form-data">
                        @csrf
                        <div class="d-flex align-items-start align-items-sm-center gap-4">
                            <img src="{{ asset($user->photo) }}" alt="{{ $user->name }}" class="d-block rounded" height="100" width="100" id="uploadedAvatar"/>
                            <div class="button-wrapper">
                                <label for="photo" class="btn btn-primary me-2 mb-4" tabindex="0">
                                    <span class="d-none d-sm-block">Upload new photo</span>
                                    <i class="bx bx-upload d-block d-sm-none"></i>
                                    <input type="file" id="photo" name="photo" class="account-file-input" hidden accept="image/png, image/jpeg"/>
                                </label>
                                <button type="button" class="btn btn-outline-secondary account-image-reset mb-4">
                                    <i class="bx bx-reset d-block d-sm-none"></i>
                                    <span class="d-none d-sm-block">Reset</span>
                                </button>
                                <p class="text-muted mb-0">Allowed JPG or PNG. Max size of 2MB</p>
                            </div>
                        </div>
                        <div class="mt-3">
                            <button type="submit" class="btn btn-primary me-2">Save Photo</button>
                        </div>
                    </form>
                </div>
                <hr class="my-0"/>
                <div class="card-body">
                    <form method="post" action="{{ route('profile.update') }}" id="formAccountSettings">
                        @csrf
                        @method('patch')
                        <div class="row">
                            <div class="mb-3 col-md-6">
                                <label for="name" class="form-label">Name</label>
                                <input class="form-control" type="text" id="name" name="name" value="{{ old('name', $user->name) }}" autofocus/>
                                <x-input-error :messages="$errors->get('name')" class="mt-2"/>
                            </div>
                            <div class="mb-3 col-md-6">
                                <label for="email" class="form-label">Email</label>
                                <input class="form-control" type="email" id="email" name="email" value="{{ old('email', $user->email) }}" placeholder="john.doe@example.com"/>
                                <x-input-error :messages="$errors->get('email')" class="mt-2"/>
                            </div>
                            <div class="mb-3 col-md-6">
                                <label class="form-label" for="phone">Phone Number</label>
                                <input type="text" id="phone" name="phone" class="form-control" value="{{ old('phone', $user->phone) }}" placeholder="+1 234 567 8910"/>
                                <x-input-error :messages="$errors->get('phone')" class="mt-2"/>
                            </div>
                            <div class="mb-3 col-md-6">
                                <label for="address" class="form-label">Address</label>
                                <input type="text" class="form-control" id="address" name="address" value="{{ old('address', $user->address) }}" placeholder="Address"/>
                                <x-input-error :messages="$errors->get('address')" class="mt-2"/>
                            </div>
                            <div class="mb-3 col-md-6">
                                <label for="city" class="form-label">City</label>
                                <input type="text" class="form-control" id="city" name="city" value="{{ old('city', $user->city) }}" placeholder="City"/>
                                <x-input-error :messages="$errors->get('city')" class="mt-2"/>
                            </div>
                            <div class="mb-3 col-md-6">
                                <label for="state" class="form-label">State</label>
                                <input class="form-control" type="text" id="state" name="state" value="{{ old('state', $user->state) }}" placeholder="State"/>
                                <x-input-error :messages="$errors->get('state')" class="mt-2"/>
                            </div>
                            <div class="mb-3 col-md-6">
                                <label for="post_code" class="form-label">Zip Code</label>
                                <input type="text" class="form-control" id="post_code" name="post_code" value="{{ old('post_code', $user->post_code) }}" placeholder="231465" maxlength="6"/>
                                <x-input-error :messages="$errors->get('post_code')" class="mt-2"/>
                            </div>
                            <div class="mb-3 col-md-6">
                                <label class="form-label" for="country">Country</label>
                                <input type="text" class="form-control" id="country" name="country" value="{{ old('country', $user->country) }}" placeholder="Country"/>
                                <x-input-error :messages="$errors->get('country')" class="mt-2"/>
                            </div>
                        </div>
                        <div class="mt-2">
                            <button type="submit" class="btn btn-primary me-2">Save changes</button>
                            <button type="reset" class="btn btn-outline-secondary">Cancel</button>
                        </div>
                    </form>
                </div>
                <!-- /Account -->
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Reset photo button
    const resetButton = document.querySelector('.account-image-reset');
    const fileInput = document.querySelector('.account-file-input');
    const accountUserImage = document.getElementById('uploadedAvatar');
    const defaultImage = '{{ asset("assets/img/avatar/default.png") }}';

    if (resetButton) {
        resetButton.addEventListener('click', function() {
            fileInput.value = '';
            accountUserImage.src = defaultImage;
        });
    }

    // Show preview of selected image
    if (fileInput) {
        fileInput.addEventListener('change', function(e) {
            if (e.target.files.length > 0) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    accountUserImage.src = e.target.result;
                };
                reader.readAsDataURL(e.target.files[0]);
            }
        });
    }
});
</script>
@endpush
</x-app-layout>

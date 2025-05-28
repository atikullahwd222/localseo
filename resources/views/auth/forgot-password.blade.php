@section('title', 'Forgot Password')
<x-guest-layout>
    <h4 class="mb-2">Forgot Password? 🔒</h4>
    <p class="mb-4">Enter your email and we'll send you instructions to reset your password</p>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}" class="mb-3">
        @csrf

        <!-- Email Address -->
        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="text" class="form-control @error('email') is-invalid @enderror" 
                id="email" name="email" value="{{ old('email') }}" 
                placeholder="Enter your email" autofocus />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <button class="btn btn-primary d-grid w-100" type="submit">Send Reset Link</button>
    </form>

    <div class="text-center">
        <a href="{{ route('login') }}" class="d-flex align-items-center justify-content-center">
            <i class="bx bx-chevron-left scaleX-n1-rtl bx-sm"></i>
            Back to login
        </a>
    </div>
</x-guest-layout>

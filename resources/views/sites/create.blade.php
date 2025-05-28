<x-app-layout>
    @section('title', 'Create Site')

    @section('content')
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h2 class="card-title mb-0">Create New Site</h2>
                            <a href="{{ route('sites.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-2"></i> Back to Sites
                            </a>
                        </div>

                        <form action="{{ route('sites.store') }}" method="POST">
                            @csrf
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="mb-3">
                                        <label for="name" class="form-label">Site Name</label>
                                        <input type="text" 
                                               class="form-control @error('name') is-invalid @enderror" 
                                               id="name" 
                                               name="name" 
                                               value="{{ old('name') }}"
                                               required>
                                        @error('name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="mb-3">
                                        <label for="url" class="form-label">Site URL</label>
                                        <input type="url" 
                                               class="form-control @error('url') is-invalid @enderror" 
                                               id="url" 
                                               name="url" 
                                               value="{{ old('url') }}"
                                               placeholder="https://"
                                               required>
                                        @error('url')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="mb-3">
                                        <label for="description" class="form-label">Description</label>
                                        <textarea class="form-control @error('description') is-invalid @enderror" 
                                                  id="description" 
                                                  name="description" 
                                                  rows="4">{{ old('description') }}</textarea>
                                        @error('description')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="mb-3 form-check">
                                        <input type="checkbox" 
                                               class="form-check-input @error('is_active') is-invalid @enderror" 
                                               id="is_active" 
                                               name="is_active" 
                                               value="1"
                                               {{ old('is_active') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_active">Active</label>
                                        @error('is_active')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="mt-4">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save me-2"></i> Create Site
                                        </button>
                                        <a href="{{ route('sites.index') }}" class="btn btn-secondary ms-2">Cancel</a>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endsection
</x-app-layout>

<x-app-layout>
    @section('title', 'Create Permission')
    
    @section('content')
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center pb-0">
                        <h6 class="mb-0">Create New Permission</h6>
                        <a href="{{ route('admin.permissions.index') }}" class="btn btn-sm btn-secondary">
                            <i class="fas fa-arrow-left me-1"></i> Back to Permissions
                        </a>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('admin.permissions.store') }}">
                            @csrf
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="name" class="form-control-label">Permission Name</label>
                                        <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                            id="name" name="name" value="{{ old('name') }}" required>
                                        @error('name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="form-text text-muted">Example: View Users, Create Posts</small>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="module" class="form-control-label">Module</label>
                                        <div class="input-group">
                                            <select class="form-select @error('module') is-invalid @enderror" 
                                                id="moduleSelect" name="module_select">
                                                <option value="">Select a module</option>
                                                @foreach($modules as $module)
                                                    <option value="{{ $module }}">{{ $module }}</option>
                                                @endforeach
                                                <option value="custom">+ Custom Module</option>
                                            </select>
                                            <input type="text" class="form-control @error('module') is-invalid @enderror" 
                                                id="moduleInput" name="module" value="{{ old('module') }}"
                                                placeholder="Enter module name" style="display: none;">
                                        </div>
                                        @error('module')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="form-text text-muted">Group related permissions together (e.g. users, posts)</small>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="description" class="form-control-label">Description</label>
                                <textarea class="form-control @error('description') is-invalid @enderror" 
                                    id="description" name="description" rows="3">{{ old('description') }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">Optional: Describe what this permission allows</small>
                            </div>
                            
                            <div class="form-group mt-4">
                                <button type="submit" class="btn btn-primary">Create Permission</button>
                                <a href="{{ route('admin.permissions.index') }}" class="btn btn-light ms-2">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endsection
    
    @push('scripts')
    <script>
        $(document).ready(function() {
            // Toggle between select and input for module
            $('#moduleSelect').change(function() {
                if ($(this).val() === 'custom') {
                    $(this).hide();
                    $('#moduleInput').show().focus().val('');
                } else {
                    $('#moduleInput').val($(this).val());
                }
            });
            
            // Handle the form submission to ensure module value is set
            $('form').on('submit', function() {
                if ($('#moduleSelect').val() !== 'custom') {
                    $('#moduleInput').val($('#moduleSelect').val());
                }
            });
        });
    </script>
    @endpush
</x-app-layout> 
<x-app-layout>
    @section('title', 'Permission Details')
    
    @section('content')
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center pb-0">
                        <h6 class="mb-0">Permission Details</h6>
                        <div>
                            <a href="{{ route('admin.permissions.edit', $permission) }}" class="btn btn-sm btn-info">
                                <i class="fas fa-pencil-alt me-1"></i> Edit
                            </a>
                            <a href="{{ route('admin.permissions.index') }}" class="btn btn-sm btn-secondary">
                                <i class="fas fa-arrow-left me-1"></i> Back to Permissions
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name" class="form-control-label">Permission Name</label>
                                    <input type="text" class="form-control" id="name" value="{{ $permission->name }}" readonly disabled>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="slug" class="form-control-label">Slug</label>
                                    <input type="text" class="form-control" id="slug" value="{{ $permission->slug }}" readonly disabled>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="module" class="form-control-label">Module</label>
                                    <input type="text" class="form-control" id="module" value="{{ $permission->module ?? 'None' }}" readonly disabled>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="created" class="form-control-label">Created At</label>
                                    <input type="text" class="form-control" id="created" value="{{ $permission->created_at->format('F j, Y H:i') }}" readonly disabled>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group mt-3">
                            <label for="description" class="form-control-label">Description</label>
                            <textarea class="form-control" id="description" rows="3" readonly disabled>{{ $permission->description }}</textarea>
                        </div>
                        
                        <hr class="horizontal dark my-4">
                        
                        <h6 class="text-uppercase text-secondary font-weight-bolder">Roles with this Permission</h6>
                        <div class="table-responsive">
                            <table class="table align-items-center mb-0">
                                <thead>
                                    <tr>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Role</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Description</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Status</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Toggle</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($roles as $role)
                                    <tr>
                                        <td>
                                            <div class="d-flex px-2 py-1">
                                                <div class="d-flex flex-column justify-content-center">
                                                    <h6 class="mb-0 text-sm">{{ ucfirst($role['name']) }}</h6>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <p class="text-xs text-secondary mb-0">{{ $role['description'] ?? 'No description' }}</p>
                                        </td>
                                        <td>
                                            @if($role['has_permission'])
                                                <span class="badge badge-sm bg-gradient-success">Granted</span>
                                            @else
                                                <span class="badge badge-sm bg-gradient-secondary">Not Granted</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($role['name'] !== 'admin')
                                            <div class="form-check form-switch">
                                                <input class="form-check-input permission-toggle" 
                                                    type="checkbox" 
                                                    id="role_{{ $role['id'] }}_toggle"
                                                    data-role-id="{{ $role['id'] }}"
                                                    data-role-name="{{ $role['name'] }}"
                                                    data-permission-id="{{ $permission->id }}"
                                                    {{ $role['has_permission'] ? 'checked' : '' }}>
                                            </div>
                                            @else
                                                <span class="badge badge-sm bg-gradient-info">Always Granted</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endsection
    
    @push('scripts')
    <script>
        $(document).ready(function() {
            // Handle permission toggle
            $('.permission-toggle').on('change', function() {
                const roleId = $(this).data('role-id');
                const permissionId = $(this).data('permission-id');
                const roleName = $(this).data('role-name');
                const hasPermission = $(this).prop('checked');
                
                // Display loader
                Swal.fire({
                    title: 'Processing...',
                    text: 'Updating permission status',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                
                $.ajax({
                    url: '{{ route("admin.permissions.assign") }}',
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        role_id: roleId,
                        permission_id: permissionId,
                        has_permission: hasPermission
                    },
                    success: function(response) {
                        Swal.close();
                        
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Success',
                                text: response.message,
                                timer: 1500,
                                showConfirmButton: false
                            }).then(() => {
                                // Update status display
                                const statusCell = $(this).closest('tr').find('td:nth-child(3)');
                                if (hasPermission) {
                                    statusCell.html('<span class="badge badge-sm bg-gradient-success">Granted</span>');
                                } else {
                                    statusCell.html('<span class="badge badge-sm bg-gradient-secondary">Not Granted</span>');
                                }
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: response.message
                            });
                            // Revert the checkbox state
                            $(this).prop('checked', !hasPermission);
                        }
                    }.bind(this),
                    error: function(xhr) {
                        Swal.close();
                        
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: xhr.responseJSON?.message || 'Failed to update permission'
                        });
                        // Revert the checkbox state
                        $(this).prop('checked', !hasPermission);
                    }.bind(this)
                });
            });
        });
    </script>
    @endpush
</x-app-layout> 
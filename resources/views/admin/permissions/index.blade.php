<x-app-layout>
    @section('title', 'Permission Management')
    
    @section('content')
    <div class="container-fluid py-4">
        <!-- Flash Messages -->
        @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif
        
        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif
        
        <div class="row">
            <div class="col-12">
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center pb-0">
                        <h6 class="mb-0">Permission Management</h6>
                        <a href="{{ route('admin.permissions.create') }}" class="btn btn-sm btn-primary">
                            <i class="fas fa-plus me-1"></i> New Permission
                        </a>
                    </div>
                    <div class="card-body px-0 pt-3 pb-2">
                        <!-- Roles Selection -->
                        <div class="px-4 mb-4">
                            <label for="roleSelector" class="form-label">Filter by Role:</label>
                            <select class="form-select" id="roleSelector">
                                <option value="0">All Roles</option>
                                @foreach($roles as $role)
                                <option value="{{ $role->id }}">{{ ucfirst($role->name) }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <!-- Permissions by Module -->
                        <div id="permissionsContainer">
                            @forelse($permissions as $module => $modulePermissions)
                            <div class="module-section mb-4">
                                <div class="px-4">
                                    <h5 class="text-uppercase text-secondary font-weight-bolder">
                                        {{ $module ?? 'General' }}
                                    </h5>
                                    <hr>
                                </div>
                                <div class="table-responsive p-0">
                                    <table class="table align-items-center mb-0">
                                        <thead>
                                            <tr>
                                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Permission</th>
                                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Slug</th>
                                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Roles</th>
                                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($modulePermissions as $permission)
                                            <tr>
                                                <td>
                                                    <div class="d-flex px-3 py-1">
                                                        <div class="d-flex flex-column justify-content-center">
                                                            <h6 class="mb-0 text-sm">{{ $permission->name }}</h6>
                                                            <p class="text-xs text-secondary mb-0">{{ $permission->description }}</p>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="badge badge-sm bg-gradient-info">{{ $permission->slug }}</span>
                                                </td>
                                                <td>
                                                    <div class="d-flex">
                                                        @foreach($roles as $role)
                                                        <div class="form-check form-switch ps-0 ms-1">
                                                            <input class="form-check-input ms-auto permission-toggle" 
                                                                type="checkbox" 
                                                                id="role_{{ $role->id }}_perm_{{ $permission->id }}"
                                                                data-role-id="{{ $role->id }}"
                                                                data-permission-id="{{ $permission->id }}"
                                                                data-role-name="{{ $role->name }}"
                                                                {{ $role->permissions->contains('id', $permission->id) ? 'checked' : '' }}
                                                                {{ $role->name === 'admin' ? 'disabled' : '' }}>
                                                            <label class="form-check-label text-body ms-3 text-truncate w-80 mb-0" for="role_{{ $role->id }}_perm_{{ $permission->id }}">
                                                                {{ ucfirst($role->name) }}
                                                            </label>
                                                        </div>
                                                        @endforeach
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="d-flex">
                                                        <a href="{{ route('admin.permissions.edit', $permission) }}" class="btn btn-link text-info px-3 mb-0">
                                                            <i class="fas fa-pencil-alt"></i>
                                                        </a>
                                                        <button type="button" class="btn btn-link text-danger px-3 mb-0 delete-permission"
                                                                data-permission-id="{{ $permission->id }}"
                                                                data-permission-name="{{ $permission->name }}">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            @empty
                            <div class="alert alert-info mx-4">
                                No permissions found. Create some permissions to get started.
                            </div>
                            @endforelse
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
            // Handle role selection for filtering
            $('#roleSelector').on('change', function() {
                const roleId = $(this).val();
                
                if (roleId == 0) {
                    // Show all permissions
                    $('.module-section').show();
                    return;
                }
                
                // Load permissions for specific role
                $.ajax({
                    url: "{{ route('admin.permissions.role', '') }}/" + roleId,
                    method: 'GET',
                    success: function(response) {
                        if (response.success) {
                            // Hide all modules first
                            $('.module-section').hide();
                            
                            // Filter and show only modules with permissions for this role
                            const rolePermissions = response.rolePermissions;
                            
                            if (rolePermissions.length === 0) {
                                $('#permissionsContainer').html(
                                    `<div class="alert alert-info mx-4">
                                        No permissions assigned to this role.
                                    </div>`
                                );
                                return;
                            }
                            
                            $('tr').each(function() {
                                const permissionId = $(this).find('.permission-toggle').data('permission-id');
                                if (permissionId && rolePermissions.includes(permissionId)) {
                                    $(this).closest('.module-section').show();
                                    $(this).show();
                                } else if (permissionId) {
                                    $(this).hide();
                                }
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Failed to load permissions for this role.'
                            });
                        }
                    },
                    error: function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Failed to load permissions for this role.'
                        });
                    }
                });
            });
            
            // Handle permission toggle
            $('.permission-toggle').on('change', function() {
                const roleId = $(this).data('role-id');
                const permissionId = $(this).data('permission-id');
                const roleName = $(this).data('role-name');
                const hasPermission = $(this).prop('checked');
                
                // Don't allow changing admin permissions
                if (roleName === 'admin') {
                    return false;
                }
                
                console.log('Sending permission toggle request:', {
                    roleId, permissionId, roleName, hasPermission
                });
                
                $.ajax({
                    url: '{{ route("admin.permissions.assign") }}',
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        role_id: roleId,
                        permission_id: permissionId,
                        has_permission: hasPermission ? 'true' : 'false'
                    },
                    success: function(response) {
                        console.log('Permission toggle success:', response);
                        if (response.success) {
                            const toastMessage = hasPermission 
                                ? `Permission granted to ${roleName} role`
                                : `Permission revoked from ${roleName} role`;
                            
                            Swal.fire({
                                icon: 'success',
                                title: 'Success',
                                text: response.message,
                                toast: true,
                                position: 'top-end',
                                showConfirmButton: false,
                                timer: 3000
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
                        console.error('Permission toggle error:', xhr.responseJSON || xhr);
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
            
            // Delete permission
            $('.delete-permission').on('click', function() {
                const permissionId = $(this).data('permission-id');
                const permissionName = $(this).data('permission-name');
                
                Swal.fire({
                    title: 'Are you sure?',
                    text: `You are about to delete the permission: "${permissionName}"`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "{{ route('admin.permissions.destroy', '') }}/" + permissionId,
                            method: 'DELETE',
                            data: {
                                _token: '{{ csrf_token() }}'
                            },
                            success: function(response) {
                                if (response.success) {
                                    Swal.fire(
                                        'Deleted!',
                                        response.message,
                                        'success'
                                    ).then(() => {
                                        window.location.reload();
                                    });
                                } else {
                                    Swal.fire(
                                        'Error!',
                                        response.message,
                                        'error'
                                    );
                                }
                            },
                            error: function(xhr) {
                                Swal.fire(
                                    'Error!',
                                    xhr.responseJSON?.message || 'Something went wrong.',
                                    'error'
                                );
                            }
                        });
                    }
                });
            });
        });
    </script>
    @endpush
</x-app-layout> 
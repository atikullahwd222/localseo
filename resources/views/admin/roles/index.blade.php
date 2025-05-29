<x-app-layout>
    @section('title', 'Role Management')

    @section('content')
        <div class="container-fluid py-4">
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Role Management</h5>
                            <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addRoleModal">
                                <i class="fas fa-plus me-1"></i> Add New Role
                            </button>
                        </div>
                        <div class="card-body">
                            @if(session('success'))
                                <div class="alert alert-success">{{ session('success') }}</div>
                            @endif
                            
                            @if(session('error'))
                                <div class="alert alert-danger">{{ session('error') }}</div>
                            @endif
                            
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Description</th>
                                            <th>Users</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($roles as $role)
                                            <tr>
                                                <td>
                                                    <span class="badge {{ $role->name === 'admin' ? 'bg-danger' : 
                                                        ($role->name === 'editor' ? 'bg-primary' : 
                                                        ($role->name === 'user' ? 'bg-secondary' : 'bg-info')) }}">
                                                        {{ ucfirst($role->name) }}
                                                    </span>
                                                </td>
                                                <td>{{ $role->description }}</td>
                                                <td>{{ $role->users_count }}</td>
                                                <td>
                                                    @if(!in_array($role->name, ['admin', 'editor', 'user']))
                                                        <div class="btn-group" role="group">
                                                            <button type="button" class="btn btn-sm btn-primary edit-role-btn" data-id="{{ $role->id }}" data-bs-toggle="modal" data-bs-target="#editRoleModal">
                                                                <i class="fas fa-edit"></i>
                                                            </button>
                                                            <button type="button" class="btn btn-sm btn-danger delete-role-btn" data-id="{{ $role->id }}">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </div>
                                                    @else
                                                        <span class="text-muted">System Role</span>
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
        
        <!-- Add Role Modal -->
        <div class="modal fade" id="addRoleModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Add New Role</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="addRoleForm">
                            @csrf
                            <div class="mb-3">
                                <label for="name" class="form-label">Role Name</label>
                                <input type="text" class="form-control" id="name" name="name" required>
                                <div class="form-text">Role names should be lowercase, without spaces (e.g. 'manager')</div>
                            </div>
                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-primary" id="saveRoleBtn">Save</button>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Edit Role Modal -->
        <div class="modal fade" id="editRoleModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Role</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="editRoleForm">
                            @csrf
                            <input type="hidden" id="edit_id" name="id">
                            <div class="mb-3">
                                <label for="edit_name" class="form-label">Role Name</label>
                                <input type="text" class="form-control" id="edit_name" name="name" required>
                                <div class="form-text">Role names should be lowercase, without spaces (e.g. 'manager')</div>
                            </div>
                            <div class="mb-3">
                                <label for="edit_description" class="form-label">Description</label>
                                <textarea class="form-control" id="edit_description" name="description" rows="3"></textarea>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-primary" id="updateRoleBtn">Update</button>
                    </div>
                </div>
            </div>
        </div>
    @endsection
    
    @push('scripts')
    <script>
        $(document).ready(function() {
            // Save new role
            $('#saveRoleBtn').on('click', function() {
                const $btn = $(this);
                
                // Disable button and show loading state
                $btn.prop('disabled', true).html(
                    '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Saving...'
                );
                
                $.ajax({
                    type: 'POST',
                    url: '{{ route("admin.roles.store") }}',
                    data: {
                        '_token': '{{ csrf_token() }}',
                        'name': $('#name').val(),
                        'description': $('#description').val()
                    },
                    success: function(response) {
                        if (response.status === 200) {
                            // Show success message and reload the page
                            Swal.fire({
                                title: 'Success!',
                                text: response.message,
                                icon: 'success'
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            // Show error message and reset button state
                            Swal.fire('Error', response.message, 'error');
                            $btn.prop('disabled', false).text('Save');
                        }
                    },
                    error: function(xhr) {
                        // Handle validation errors
                        if (xhr.status === 422) {
                            let errorMessages = '';
                            const errors = xhr.responseJSON.errors;
                            
                            for (const field in errors) {
                                errors[field].forEach(function(message) {
                                    errorMessages += `• ${message}<br>`;
                                });
                            }
                            
                            Swal.fire({
                                title: 'Validation Error',
                                html: errorMessages,
                                icon: 'error'
                            });
                        } else {
                            Swal.fire('Error', 'Something went wrong. Please try again.', 'error');
                        }
                        
                        $btn.prop('disabled', false).text('Save');
                    }
                });
            });
            
            // Load role data for editing
            $('.edit-role-btn').on('click', function() {
                const roleId = $(this).data('id');
                
                $.ajax({
                    type: 'GET',
                    url: `{{ url('admin/roles') }}/${roleId}/edit`,
                    success: function(response) {
                        if (response.status === 200) {
                            $('#edit_id').val(response.role.id);
                            $('#edit_name').val(response.role.name);
                            $('#edit_description').val(response.role.description);
                        } else {
                            Swal.fire('Error', response.message, 'error');
                        }
                    },
                    error: function() {
                        Swal.fire('Error', 'Failed to load role data', 'error');
                    }
                });
            });
            
            // Update role
            $('#updateRoleBtn').on('click', function() {
                const $btn = $(this);
                const roleId = $('#edit_id').val();
                
                // Disable button and show loading state
                $btn.prop('disabled', true).html(
                    '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Updating...'
                );
                
                $.ajax({
                    type: 'PUT',
                    url: `{{ url('admin/roles') }}/${roleId}`,
                    data: {
                        '_token': '{{ csrf_token() }}',
                        'name': $('#edit_name').val(),
                        'description': $('#edit_description').val()
                    },
                    success: function(response) {
                        if (response.status === 200) {
                            // Show success message and reload the page
                            Swal.fire({
                                title: 'Success!',
                                text: response.message,
                                icon: 'success'
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            // Show error message and reset button state
                            Swal.fire('Error', response.message, 'error');
                            $btn.prop('disabled', false).text('Update');
                        }
                    },
                    error: function(xhr) {
                        // Handle validation errors
                        if (xhr.status === 422) {
                            let errorMessages = '';
                            const errors = xhr.responseJSON.errors;
                            
                            for (const field in errors) {
                                errors[field].forEach(function(message) {
                                    errorMessages += `• ${message}<br>`;
                                });
                            }
                            
                            Swal.fire({
                                title: 'Validation Error',
                                html: errorMessages,
                                icon: 'error'
                            });
                        } else {
                            Swal.fire('Error', 'Something went wrong. Please try again.', 'error');
                        }
                        
                        $btn.prop('disabled', false).text('Update');
                    }
                });
            });
            
            // Delete role
            $('.delete-role-btn').on('click', function() {
                const roleId = $(this).data('id');
                
                Swal.fire({
                    title: 'Are you sure?',
                    text: "You want to delete this role? All users with this role will be moved to the default 'user' role.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            type: 'DELETE',
                            url: `{{ url('admin/roles') }}/${roleId}`,
                            data: {
                                '_token': '{{ csrf_token() }}'
                            },
                            success: function(response) {
                                if (response.status === 200) {
                                    // Show success message and reload the page
                                    Swal.fire({
                                        title: 'Deleted!',
                                        text: response.message,
                                        icon: 'success'
                                    }).then(() => {
                                        location.reload();
                                    });
                                } else {
                                    // Show error message
                                    Swal.fire('Error', response.message, 'error');
                                }
                            },
                            error: function() {
                                Swal.fire('Error', 'Failed to delete role', 'error');
                            }
                        });
                    }
                });
            });
        });
    </script>
    @endpush
</x-app-layout> 
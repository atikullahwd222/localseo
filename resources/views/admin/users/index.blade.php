<x-app-layout>
    @section('title', 'User Management')

    @section('content')
        <div class="container-fluid py-4">
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">User Management</h5>
                            <div>
                                <a href="{{ route('admin.users.pending') }}" class="btn btn-sm btn-warning me-2">
                                    <i class="fas fa-user-clock me-1"></i> Pending Approvals
                                </a>
                                <a href="{{ route('admin.roles.index') }}" class="btn btn-sm btn-primary">
                                    <i class="fas fa-user-tag me-1"></i> Roles
                                </a>
                            </div>
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
                                            <th>Email</th>
                                            <th>Role</th>
                                            <th>Status</th>
                                            <th>Registered</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($users as $user)
                                            <tr>
                                                <td>{{ $user->name }}</td>
                                                <td>{{ $user->email }}</td>
                                                <td>
                                                    <span class="badge {{ $user->role->name === 'admin' ? 'bg-danger' : 
                                                       ($user->role->name === 'editor' ? 'bg-primary' : 'bg-secondary') }}">
                                                        {{ ucfirst($user->role->name ?? 'User') }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="badge {{ $user->status === 'active' ? 'bg-success' : 
                                                       ($user->status === 'inactive' ? 'bg-warning' : 'bg-danger') }}">
                                                        {{ ucfirst($user->status) }}
                                                    </span>
                                                </td>
                                                <td>{{ $user->created_at->format('M d, Y') }}</td>
                                                <td>
                                                    @if(auth()->id() !== $user->id)
                                                        <div class="dropdown">
                                                            <button class="btn btn-sm btn-primary dropdown-toggle" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                                                                Actions
                                                            </button>
                                                            <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                                                <li>
                                                                    <a class="dropdown-item change-role-btn" href="#" data-id="{{ $user->id }}" data-bs-toggle="modal" data-bs-target="#changeRoleModal">
                                                                        <i class="fas fa-user-tag me-2"></i> Change Role
                                                                    </a>
                                                                </li>
                                                                <li>
                                                                    <a class="dropdown-item change-status-btn" href="#" data-id="{{ $user->id }}" data-bs-toggle="modal" data-bs-target="#changeStatusModal">
                                                                        <i class="fas fa-user-shield me-2"></i> Change Status
                                                                    </a>
                                                                </li>
                                                            </ul>
                                                        </div>
                                                    @else
                                                        <span class="text-muted">Current User</span>
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

        <!-- Change Role Modal -->
        <div class="modal fade" id="changeRoleModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Change User Role</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="changeRoleForm">
                            @csrf
                            <input type="hidden" id="user_id" name="user_id">
                            <div class="mb-3">
                                <label for="role_id" class="form-label">Select Role</label>
                                <select class="form-select" id="role_id" name="role_id" required>
                                    @foreach($roles as $role)
                                        <option value="{{ $role->id }}">{{ ucfirst($role->name) }} - {{ $role->description }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-primary" id="saveRoleBtn">Save Changes</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Change Status Modal -->
        <div class="modal fade" id="changeStatusModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Change User Status</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="changeStatusForm">
                            @csrf
                            <input type="hidden" id="status_user_id" name="user_id">
                            <div class="mb-3">
                                <label for="status" class="form-label">Select Status</label>
                                <select class="form-select" id="status" name="status" required>
                                    <option value="active">Active - User can login and access resources</option>
                                    <option value="inactive">Inactive - User registration is pending approval</option>
                                    <option value="suspended">Suspended - User account has been temporarily disabled</option>
                                </select>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-primary" id="saveStatusBtn">Save Changes</button>
                    </div>
                </div>
            </div>
        </div>
    @endsection
    
    @push('scripts')
    <script>
        $(document).ready(function() {
            // Handle change role button click
            $('.change-role-btn').on('click', function() {
                const userId = $(this).data('id');
                $('#user_id').val(userId);
            });
            
            // Handle save role button click
            $('#saveRoleBtn').on('click', function() {
                const userId = $('#user_id').val();
                const roleId = $('#role_id').val();
                const $btn = $(this);
                
                // Disable button and show loading state
                $btn.prop('disabled', true).html(
                    '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Saving...'
                );
                
                $.ajax({
                    type: 'POST',
                    url: `{{ url('/admin/users') }}/${userId}/role`,
                    data: {
                        '_token': '{{ csrf_token() }}',
                        'role_id': roleId
                    },
                    success: function(response) {
                        if (response.status === 200) {
                            // Show success message and reload the page
                            Swal.fire({
                                title: 'Success!',
                                text: response.message || 'User role changed successfully!',
                                icon: 'success'
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            // Show error message and reset button state
                            Swal.fire('Error', response.message || 'Failed to change user role', 'error');
                            $btn.prop('disabled', false).text('Save Changes');
                        }
                    },
                    error: function(xhr) {
                        // Show error message and reset button state
                        Swal.fire('Error', xhr.responseJSON?.message || 'An error occurred', 'error');
                        $btn.prop('disabled', false).text('Save Changes');
                    }
                });
            });
            
            // Handle change status button click
            $('.change-status-btn').on('click', function() {
                const userId = $(this).data('id');
                $('#status_user_id').val(userId);
            });
            
            // Handle save status button click
            $('#saveStatusBtn').on('click', function() {
                const userId = $('#status_user_id').val();
                const status = $('#status').val();
                const $btn = $(this);
                
                // Disable button and show loading state
                $btn.prop('disabled', true).html(
                    '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Saving...'
                );
                
                $.ajax({
                    type: 'POST',
                    url: `{{ url('/admin/users') }}/${userId}/status`,
                    data: {
                        '_token': '{{ csrf_token() }}',
                        'status': status
                    },
                    success: function(response) {
                        if (response.status === 200) {
                            // Show success message and reload the page
                            Swal.fire({
                                title: 'Success!',
                                text: response.message || 'User status changed successfully!',
                                icon: 'success'
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            // Show error message and reset button state
                            Swal.fire('Error', response.message || 'Failed to change user status', 'error');
                            $btn.prop('disabled', false).text('Save Changes');
                        }
                    },
                    error: function(xhr) {
                        // Show error message and reset button state
                        Swal.fire('Error', xhr.responseJSON?.message || 'An error occurred', 'error');
                        $btn.prop('disabled', false).text('Save Changes');
                    }
                });
            });
        });
    </script>
    @endpush
</x-app-layout> 
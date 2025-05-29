<x-app-layout>
    @section('title', 'Pending Users')

    @section('content')
        <div class="container-fluid py-4">
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Pending User Approvals</h5>
                            @if(auth()->user()->isAdmin())
                            <a href="{{ route('admin.users.index') }}" class="btn btn-sm btn-primary">
                                <i class="fas fa-users me-1"></i> All Users
                            </a>
                            @endif
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
                                            <th>Registered At</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($pendingUsers as $user)
                                            <tr>
                                                <td>{{ $user->name }}</td>
                                                <td>{{ $user->email }}</td>
                                                <td>{{ $user->created_at->format('M d, Y H:i') }}</td>
                                                <td><span class="badge bg-warning">Inactive</span></td>
                                                <td>
                                                    <button class="btn btn-success btn-sm approve-user-btn" data-id="{{ $user->id }}">
                                                        <i class="fas fa-check me-1"></i> Approve
                                                    </button>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center">
                                                    <div class="py-4">
                                                        <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                                                        <h4>No pending approvals!</h4>
                                                        <p class="text-muted">All users have been approved</p>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforelse
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
            // Handle approve user button click
            $('.approve-user-btn').on('click', function() {
                const userId = $(this).data('id');
                const $btn = $(this);
                
                // Disable button and show loading state
                $btn.prop('disabled', true).html(
                    '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Approving...'
                );
                
                $.ajax({
                    type: 'POST',
                    url: `{{ url('/admin/users') }}/${userId}/approve`,
                    data: {
                        '_token': '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.status === 200) {
                            // Show success message
                            Swal.fire({
                                title: 'Success!',
                                text: response.message || 'User approved successfully!',
                                icon: 'success'
                            }).then(() => {
                                // Remove the row or reload the page
                                $btn.closest('tr').fadeOut(function() {
                                    $(this).remove();
                                    
                                    // If no more rows, show empty message
                                    if ($('table tbody tr').length === 0) {
                                        $('table tbody').append(`
                                            <tr>
                                                <td colspan="5" class="text-center">
                                                    <div class="py-4">
                                                        <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                                                        <h4>No pending approvals!</h4>
                                                        <p class="text-muted">All users have been approved</p>
                                                    </div>
                                                </td>
                                            </tr>
                                        `);
                                    }
                                });
                            });
                        } else {
                            // Show error message and reset button state
                            Swal.fire('Error', response.message || 'Failed to approve user', 'error');
                            $btn.prop('disabled', false).html('<i class="fas fa-check me-1"></i> Approve');
                        }
                    },
                    error: function(xhr) {
                        // Show error message and reset button state
                        Swal.fire('Error', xhr.responseJSON?.message || 'An error occurred', 'error');
                        $btn.prop('disabled', false).html('<i class="fas fa-check me-1"></i> Approve');
                    }
                });
            });
        });
    </script>
    @endpush
</x-app-layout> 
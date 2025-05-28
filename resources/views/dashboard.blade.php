<x-app-layout>
    @section('title', 'Dashboard')

    @section('content')
        <div class="container-fluid py-4">
            <!-- Flash Messages -->
            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="row">
                <!-- User Info Card -->
                <div class="col-xl-4 col-sm-6 mb-4">
                    <div class="card border-0 shadow">
                        <div class="card-body p-3">
                            <div class="row">
                                <div class="col-8">
                                    <div class="numbers">
                                        <p class="text-sm mb-0 text-uppercase font-weight-bold">Current User</p>
                                        <h5 class="font-weight-bolder">
                                            {{ auth()->user()->name }}
                                        </h5>
                                        <p class="mb-0 text-sm">
                                            <span class="text-success text-sm font-weight-bolder">Email: </span>
                                            {{ auth()->user()->email }}
                                        </p>
                                    </div>
                                </div>
                                <div class="col-4 text-end">
                                    <div
                                        class="icon icon-shape bg-gradient-primary shadow-primary text-center rounded-circle">
                                        <i class="fas fa-user text-lg opacity-10" aria-hidden="true"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Active Sessions Count -->
                <div class="col-xl-4 col-sm-6 mb-4">
                    <div class="card border-0 shadow">
                        <div class="card-body p-3">
                            <div class="row">
                                <div class="col-8">
                                    <div class="numbers">
                                        <p class="text-sm mb-0 text-uppercase font-weight-bold">Active Sessions</p>
                                        <h5 class="font-weight-bolder" id="activeSessionsCount">
                                            <div class="spinner-border spinner-border-sm" role="status">
                                                <span class="visually-hidden">Loading...</span>
                                            </div>
                                        </h5>
                                        <p class="mb-0 text-sm">
                                            <span class="text-success text-sm font-weight-bolder">Online </span>
                                            users
                                        </p>
                                    </div>
                                </div>
                                <div class="col-4 text-end">
                                    <div
                                        class="icon icon-shape bg-gradient-success shadow-success text-center rounded-circle">
                                        <i class="fas fa-users text-lg opacity-10" aria-hidden="true"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Last Login -->
                <div class="col-xl-4 col-sm-6 mb-4">
                    <div class="card border-0 shadow">
                        <div class="card-body p-3">
                            <div class="row">
                                <div class="col-8">
                                    <div class="numbers">
                                        <p class="text-sm mb-0 text-uppercase font-weight-bold">Last Login</p>
                                        <h5 class="font-weight-bolder">
                                            {{ auth()->user()->last_login_at ? auth()->user()->last_login_at->diffForHumans() : 'N/A' }}
                                        </h5>
                                        <p class="mb-0 text-sm">
                                            <span class="text-success text-sm font-weight-bolder">IP: </span>
                                            {{ auth()->user()->last_login_ip ?? 'N/A' }}
                                        </p>
                                    </div>
                                </div>
                                <div class="col-4 text-end">
                                    <div
                                        class="icon icon-shape bg-gradient-warning shadow-warning text-center rounded-circle">
                                        <i class="fas fa-clock text-lg opacity-10" aria-hidden="true"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Active Sessions Table -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card mb-4 border-0 shadow">
                        <div class="card-header pb-0 bg-white border-0">
                            <div class="d-flex align-items-center">
                                <h6 class="mb-0">Active Sessions</h6>
                                <button class="btn btn-sm btn-outline-primary ms-auto refresh-sessions">
                                    <i class="fas fa-sync-alt me-1"></i> Refresh
                                </button>
                            </div>
                        </div>
                        <div class="card-body px-0 pt-0 pb-2">
                            <div class="table-responsive p-0">
                                <table class="table align-items-center mb-0">
                                    <thead>
                                        <tr>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                                User</th>
                                            <th
                                                class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                                IP Address</th>
                                            <th
                                                class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                                Last Activity</th>
                                            <th
                                                class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                                Device</th>
                                            <th
                                                class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                                Browser</th>
                                            <th
                                                class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                                Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody id="activeSessions">
                                        <tr>
                                            <td colspan="6" class="text-center">
                                                <div class="d-flex justify-content-center py-4">
                                                    <div class="spinner-border text-primary" role="status">
                                                        <span class="visually-hidden">Loading...</span>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
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
                function fetchActiveSessions() {
                    console.log('Fetching active sessions...');
                    $.ajax({
                        url: '{{ route('sessions.active') }}',
                        method: 'GET',
                        success: function(response) {
                            console.log('Sessions response:', response);
                            $('#activeSessionsCount').text(response.count);

                            if (response.count === 0) {
                                $('#activeSessions').html(
                                    '<tr><td colspan="6" class="text-center">No active sessions found</td></tr>'
                                    );
                                return;
                            }

                            let tableContent = '';
                            response.sessions.forEach(function(session) {
                                // Determine badge and style for activity status
                                let activityClass = 'bg-success';
                                if (session.last_activity.includes('minutes') || session
                                    .last_activity.includes('hours')) {
                                    activityClass = 'bg-warning';
                                }

                                tableContent += `
                                <tr>
                                    <td>
                                        <div class="d-flex px-2 py-1">
                                            <div class="d-flex flex-column justify-content-center">
                                                <h6 class="mb-0 text-sm">${session.user.name}</h6>
                                                <p class="text-xs text-secondary mb-0">${session.user.email}</p>
        </div>
    </div>
                                    </td>
                                    <td>
                                        <p class="text-xs font-weight-bold mb-0">${session.ip_address}</p>
                                    </td>
                                    <td>
                                        <span class="text-xs font-weight-bold">
                                            <span class="badge badge-sm ${activityClass}">${session.last_activity}</span>
                                        </span>
                                    </td>
                                    <td>
                                        <p class="text-xs font-weight-bold mb-0">
                                            <i class="fas fa-${session.device.toLowerCase() === 'mobile' ? 'mobile-alt' :
                                                         session.device.toLowerCase() === 'tablet' ? 'tablet-alt' : 'desktop'} me-1"></i>
                                            ${session.device}
                                        </p>
                                    </td>
                                    <td>
                                        <p class="text-xs font-weight-bold mb-0">${session.browser}</p>
                                    </td>
                                    <td>
                                        ${session.is_current ?
                                            '<span class="badge badge-sm bg-success">Current Session</span>' :
                                            @if (auth()->user()->role === 'admin')
                                            `<button class="btn btn-sm btn-danger terminate-session" data-session-id="${session.id}">
                                                        <i class="fas fa-times-circle me-1"></i> Terminate
                                                    </button>`
                                            @else
                                            '<span class="badge badge-sm bg-secondary">No Permission</span>'
                                            @endif
                                        }
                                    </td>
                                </tr>
                            `;
                            });

                            $('#activeSessions').html(tableContent);
                        },
                        error: function(xhr, status, error) {
                            console.error('Error fetching sessions:', xhr.responseJSON || xhr);
                            let errorMessage = 'Failed to load active sessions.';

                            if (xhr.responseJSON && xhr.responseJSON.error) {
                                errorMessage = xhr.responseJSON.error;
                                console.error('Server error:', errorMessage);
                            }

                            $('#activeSessionsCount').text('Error');
                            $('#activeSessions').html(`
                            <tr>
                                <td colspan="6" class="text-center text-danger p-3">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    Error: ${errorMessage}
                                </td>
                            </tr>
                        `);

                            Swal.fire({
                                title: 'Error!',
                                text: errorMessage,
                                icon: 'error',
                                confirmButtonText: 'Ok'
                            });
                        }
                    });
                }

                // Initial fetch
                fetchActiveSessions();

                // Refresh button
                $('.refresh-sessions').on('click', function() {
                    $(this).html('<i class="fas fa-spinner fa-spin me-1"></i> Refreshing...');
                    fetchActiveSessions();
                    setTimeout(() => {
                        $(this).html('<i class="fas fa-sync-alt me-1"></i> Refresh');
                    }, 1000);
                });

                // Refresh every 30 seconds
                setInterval(fetchActiveSessions, 30000);

                // Handle session termination
                $(document).on('click', '.terminate-session', function() {
                    const sessionId = $(this).data('session-id');
                    const button = $(this);

                    Swal.fire({
                        title: 'Are you sure?',
                        text: "This will terminate the user's session.",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Yes, terminate it!'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            button.prop('disabled', true)
                                .html('<i class="fas fa-spinner fa-spin"></i>');

                            $.ajax({
                                url: "{{ url('sessions/terminate') }}/" + sessionId,
                                method: 'POST',
                                data: {
                                    _token: '{{ csrf_token() }}'
                                },
                                success: function(response) {
                                    console.log('Terminate response:', response);
                                    if (response.success) {
                                        Swal.fire({
                                            title: 'Terminated!',
                                            text: 'The session has been terminated.',
                                            icon: 'success',
                                            timer: 2000,
                                            showConfirmButton: false
                                        });
                                        fetchActiveSessions();
                                    } else {
                                        button.prop('disabled', false).html(
                                            '<i class="fas fa-times-circle me-1"></i> Terminate'
                                            );
                                        Swal.fire(
                                            'Error!',
                                            response.message ||
                                            'Failed to terminate session.',
                                            'error'
                                        );
                                    }
                                },
                                error: function(xhr, status, error) {
                                    console.error('Error terminating session:', xhr
                                        .responseJSON || xhr);
                                    button.prop('disabled', false).html(
                                        '<i class="fas fa-times-circle me-1"></i> Terminate'
                                        );

                                    let errorMessage = 'Failed to terminate session.';
                                    if (xhr.responseJSON && xhr.responseJSON.message) {
                                        errorMessage = xhr.responseJSON.message;
                                    }

                                    Swal.fire(
                                        'Error!',
                                        errorMessage,
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

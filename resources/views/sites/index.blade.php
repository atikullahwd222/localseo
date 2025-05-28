<x-app-layout>
    @section('title', 'Sites')

    @section('content')
        <div class="container-fluid py-4">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <h2 class="card-title mb-0">Manage Sites</h2>
                                <a href="#" data-bs-toggle="modal" data-bs-target="#AddSiteModal"
                                    class="btn btn-primary">
                                    <i class="fas fa-plus me-2"></i> Add New Site
                                </a>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th scope="col">Site Name</th>
                                            <th scope="col">URL</th>
                                            <th scope="col">Status</th>
                                            <th scope="col">Last Scan</th>
                                            <th scope="col">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>

                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>



        <!-- Add Modal -->
        <div class="modal fade" id="AddSiteModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">

                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="exampleModalLabel">Add Site</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                        <form id="addSiteForm" action="" method="post">
                            @csrf
                            <div class="form-group">
                                <label for="name">Site Name</label>
                                <input type="text" class="form-control" id="name" name="name" required>
                            </div>

                            <div class="form-group">
                                <label for="url">Site URL</label>
                                <input type="url" class="form-control" id="url" name="url" required>
                            </div>

                            <div class="form-group">
                                <label for="description">Description</label>
                                <textarea class="form-control" id="description" name="description"></textarea>
                            </div>

                            <div class="form-group">
                                <label for="status">Status</label>
                                <select class="form-control" id="status" name="status">
                                    <option value="active" selected>Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="type">Type</label>
                                <select class="form-control" id="type" name="type">
                                    <option value="general" selected>General</option>
                                    <option value="blog">Blog</option>
                                    <option value="shop">Shop</option>
                                    <option value="portfolio">Portfolio</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="theme">Theme</label>
                                <input type="text" class="form-control" id="theme" name="theme" value="default">
                            </div>
                        </form>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <!-- Save Changes button submits the form -->
                        <button type="button" class="btn btn-primary saveSiteBtn" id="saveSiteBtn">Save changes</button>
                    </div>

                </div>
            </div>
        </div>


        <!-- Edit Modal -->
        <!-- Edit Modal -->
        <div class="modal fade" id="EditModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">

                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="editModalLabel">Edit Site</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                        <form id="editSiteForm" method="post">
                            @csrf
                            <input type="hidden" id="edit_id" name="edit_id">

                            <div class="form-group mb-2">
                                <label for="edit_name">Site Name</label>
                                <input type="text" class="form-control" id="edit_name" name="edit_name" required>
                            </div>

                            <div class="form-group mb-2">
                                <label for="edit_url">Site URL</label>
                                <input type="url" class="form-control" id="edit_url" name="edit_url" required>
                            </div>

                            <div class="form-group mb-2">
                                <label for="edit_description">Description</label>
                                <textarea class="form-control" id="edit_description" name="edit_description"></textarea>
                            </div>

                            <div class="form-group mb-2">
                                <label for="edit_status">Status</label>
                                <select class="form-control" id="edit_status" name="edit_status">
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>

                            <div class="form-group mb-2">
                                <label for="edit_type">Type</label>
                                <select class="form-control" id="edit_type" name="edit_type">
                                    <option value="general">General</option>
                                    <option value="blog">Blog</option>
                                    <option value="shop">Shop</option>
                                    <option value="portfolio">Portfolio</option>
                                </select>
                            </div>

                            <div class="form-group mb-2">
                                <label for="edit_theme">Theme</label>
                                <input type="text" class="form-control" id="edit_theme" name="edit_theme"
                                    value="default">
                            </div>
                        </form>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-primary edit_btn">Save changes</button>
                    </div>

                </div>
            </div>
        </div>



    @endsection

    @push('scripts')
        <script>
            $(document).ready(function() {

                fetchSites();

                function fetchSites() {
                    $.ajax({
                        type: "GET",
                        url: "{{ route('sites.fetch') }}",
                        dataType: "json",
                        success: function(response) {
                            $('tbody').html("");
                            $.each(response.sites, function(index, site) {
                                $('tbody').append(`
                                    <tr>
                                        <td>${site.name}</td>
                                        <td>${site.url}</td>
                                        <td>${site.status}</td>
                                        <td>${site.last_scan ? site.last_scan : 'N/A'}</td>
                                        <td>
                                            <div class="btn-group" role="group" aria-label="Basic example">
                                                <button type="button" value="${site.id}" class="btn btn-primary edit-btn"><i class='bx bxs-edit' ></i></button>
                                                <button type="button" class="btn btn-danger"><i class='bx bxs-trash'></i></button>
                                            </div>
                                        </td>
                                    </tr>
                                `);
                            });
                        },
                        error: function(xhr) {
                            console.error("Error fetching sites:", xhr);
                        }
                    });
                }

                $('tbody').on('click', '.edit-btn', function(e) {
                    e.preventDefault();
                    var siteId = $(this).val();
                    $.ajax({
                        type: "GET",
                        url: "{{ route('sites.edit', '') }}/" + siteId,
                        success: function(response) {
                            if (response.status === 200) {
                                $('#EditModal').modal('show');
                                $('#edit_id').val(response.site.id);
                                $('#edit_name').val(response.site.name);
                                $('#edit_url').val(response.site.url);
                                $('#edit_description').val(response.site.description);
                                $('#edit_status').val(response.site.status);
                                $('#edit_type').val(response.site.type);
                                $('#edit_theme').val(response.site.theme);
                            } else {
                                Swal.fire({
                                    title: "Error!",
                                    text: response.message ||
                                        "Failed to fetch site details.",
                                    icon: "error",
                                    allowOutsideClick: true,
                                    showConfirmButton: true,
                                    didOpen: () => {
                                        const popup = Swal.getPopup();
                                        popup.setAttribute('draggable', 'true');
                                    }
                                });
                            }
                        }
                    });
                });

                $('.edit_btn').on('click', function(e) {
                    e.preventDefault();

                    const formData = {
                        id: $('#edit_id').val(),
                        name: $('#edit_name').val(),
                        url: $('#edit_url').val(),
                        description: $('#edit_description').val(),
                        status: $('#edit_status').val(),
                        type: $('#edit_type').val(),
                        theme: $('#edit_theme').val(),
                        _token: $('meta[name="csrf-token"]').attr('content')
                    };

                    $.ajax({
                        type: "PUT",
                        url: "{{ route('sites.update', '') }}/" + formData.id,
                        data: formData,
                        dataType: "json",
                        success: function(response) {
                            if (response.status === 200) {
                                $('#EditModal').modal('hide');
                                Swal.fire({
                                    title: "Site Updated!",
                                    text: response.message || "Site updated successfully.",
                                    icon: "success",
                                    allowOutsideClick: true,
                                    showConfirmButton: true,
                                    didOpen: () => {
                                        const popup = Swal.getPopup();
                                        popup.setAttribute('draggable', 'true');
                                    }
                                }).then(() => {
                                    fetchSites();
                                });
                            } else if(response.status === 200){
                                Swal.fire({
                                    title: "Site Updated Failed!",
                                    text: response.message || "Site Not found.",
                                    icon: "error",
                                    allowOutsideClick: true,
                                    showConfirmButton: true,
                                    didOpen: () => {
                                        const popup = Swal.getPopup();
                                        popup.setAttribute('draggable', 'true');
                                    }
                                })
                            } else {
                                Swal.fire("Error", response.message || "Something went wrong.",
                                    "error");
                            }
                        },
                        error: function(xhr) {
                            $('#EditModal').modal('hide');
                            Swal.fire("Error", "Update failed. Please try again.", "error");
                        }
                    });
                });



                $('.saveSiteBtn').on('click', function(e) {
                    e.preventDefault();

                    const formData = {
                        name: $('#name').val(),
                        url: $('#url').val(),
                        description: $('#description').val(),
                        status: $('#status').val(),
                        type: $('#type').val(),
                        theme: $('#theme').val(),
                        _token: $('meta[name="csrf-token"]').attr('content')
                    };

                    $.ajax({
                        type: "POST",
                        url: "{{ route('sites.store') }}",
                        data: formData,
                        dataType: "json",
                        success: function(response) {
                            $('#AddSiteModal').modal('hide');
                            Swal.fire({
                                title: response.status === 200 ? "Site Created!" : "Error!",
                                text: response.message || (response.status === 200 ?
                                    "Site added successfully." : "Something went wrong."
                                ),
                                icon: response.status === 200 ? "success" : "error",
                                allowOutsideClick: true,
                                showConfirmButton: true,
                                didOpen: () => {
                                    const popup = Swal.getPopup();
                                    popup.setAttribute('draggable', 'true');
                                    fetchSites();
                                }
                            });
                        },
                        error: function(xhr) {
                            $('#AddSiteModal').modal('hide');
                            Swal.fire({
                                title: "Error!",
                                text: xhr.responseJSON?.message ||
                                    "Failed to add site. Please try again.",
                                icon: "error",
                                allowOutsideClick: true,
                                showConfirmButton: true,
                                didOpen: () => {
                                    const popup = Swal.getPopup();
                                    popup.setAttribute('draggable', 'true');
                                }
                            });
                        }
                    });
                });
            });
        </script>
    @endpush

</x-app-layout>

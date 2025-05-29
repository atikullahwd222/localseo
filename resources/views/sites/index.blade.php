<x-app-layout>
    @section('title', 'Sites')

    @section('styles')
    <style>
        .filter-step {
            position: relative;
            padding: 15px;
            border-radius: 5px;
            transition: all 0.3s ease;
        }
        
        .filter-step.active {
            animation: pulse 1s;
        }
        
        @keyframes pulse {
            0% {
                background-color: rgba(var(--bs-primary-rgb), 0.05);
            }
            50% {
                background-color: rgba(var(--bs-primary-rgb), 0.1);
            }
            100% {
                background-color: transparent;
            }
        }
        
        .next-step, .prev-step {
            transition: all 0.2s;
        }
        
        .next-step:hover, .prev-step:hover {
            transform: translateY(-2px);
        }
        
        .badge {
            font-size: 0.8rem;
            padding: 0.35em 0.65em;
        }
        
        /* Rating colors */
        .rating-high {
            color: #28a745;
            font-weight: bold;
        }
        
        .rating-medium {
            color: #ffc107;
        }
        
        .rating-low {
            color: #dc3545;
        }
        
        /* Filter dependency badges */
        .category-badge {
            font-size: 0.8rem;
            margin-right: 5px;
            margin-bottom: 5px;
            display: inline-block;
        }
        
        .unsupported-option {
            opacity: 0.5;
            text-decoration: line-through;
        }
        
        .compatibility-note {
            font-size: 0.8rem;
            color: #6c757d;
            margin-top: 5px;
            display: block;
        }
    </style>
    @endsection

    @section('content')
        <div class="container-fluid py-4">
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Filter Sites</h5>
                            <button class="btn btn-sm btn-secondary" id="resetFilters">Reset Filters</button>
                        </div>
                        <div class="card-body">
                            <div class="filter-steps">
                                <!-- Step 1: Select Category -->
                                <div id="step1" class="filter-step active">
                                    <h6 class="mb-3">Step 1: Select Categories</h6>
                                    
                                    <div class="alert alert-info mb-3" role="alert">
                                        <i class="fas fa-info-circle me-2"></i>
                                        <strong>Category-Based Filtering:</strong> Your category selection will determine which countries, work purposes, and features are available in the following steps.
                                    </div>
                                    
                                    <div class="mb-3 border p-3 rounded" style="max-height: 200px; overflow-y: auto;">
                                        <div class="row">
                                            @foreach($categories as $category)
                                                <div class="col-md-4 mb-2">
                                                    <div class="form-check">
                                                        <input class="form-check-input filter-category" type="checkbox" 
                                                            value="{{ $category->id }}" id="filter_category{{ $category->id }}"
                                                            data-category-name="{{ $category->name }}">
                                                        <label class="form-check-label" for="filter_category{{ $category->id }}">
                                                            {{ $category->name }}
                                                        </label>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-end">
                                        <button class="btn btn-primary next-step" data-next="step2">Next</button>
                                    </div>
                                </div>

                                <!-- Step 2: Select Countries -->
                                <div id="step2" class="filter-step d-none">
                                    <h6 class="mb-3">Step 2: Select Countries</h6>
                                    <div id="selectedCategoriesBadges" class="mb-3"></div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" id="filter_global">
                                        <label class="form-check-label" for="filter_global">
                                            <strong>Global (All Countries)</strong>
                                        </label>
                                    </div>
                                    <div id="filter_countriesContainer" class="mb-3 border p-3 rounded" style="max-height: 200px; overflow-y: auto;">
                                        <div class="row">
                                            @foreach($countries as $country)
                                                <div class="col-md-4 mb-2">
                                                    <div class="form-check">
                                                        <input class="form-check-input filter-country" type="checkbox" 
                                                            value="{{ $country->id }}" id="filter_country{{ $country->id }}">
                                                        <label class="form-check-label" for="filter_country{{ $country->id }}">
                                                            {{ $country->name }}
                                                        </label>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <button class="btn btn-secondary prev-step" data-prev="step1">Previous</button>
                                        <button class="btn btn-primary next-step" data-next="step3">Next</button>
                                    </div>
                                </div>

                                <!-- Step 3: Select Work Purposes -->
                                <div id="step3" class="filter-step d-none">
                                    <h6 class="mb-3">Step 3: Select Work Purposes</h6>
                                    <div id="selectedCategoriesBadges2" class="mb-3"></div>
                                    <div class="mb-3 border p-3 rounded" style="max-height: 200px; overflow-y: auto;">
                                        <div class="row">
                                            @foreach($purposes as $purpose)
                                                <div class="col-md-4 mb-2">
                                                    <div class="form-check">
                                                        <input class="form-check-input filter-purpose" type="checkbox" 
                                                            value="{{ $purpose->id }}" id="filter_purpose{{ $purpose->id }}">
                                                        <label class="form-check-label" for="filter_purpose{{ $purpose->id }}">
                                                            {{ $purpose->name }}
                                                        </label>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <button class="btn btn-secondary prev-step" data-prev="step2">Previous</button>
                                        <button class="btn btn-primary next-step" data-next="step4">Next</button>
                                    </div>
                                </div>

                                <!-- Step 4: Select Minimum Rating -->
                                <div id="step4" class="filter-step d-none">
                                    <h6 class="mb-3">Step 4: Select Minimum Rating</h6>
                                    <div id="selectedCategoriesBadges3" class="mb-3"></div>
                                    <div class="mb-3">
                                        <label for="filter_rating" class="form-label">Minimum Rating: <span id="ratingValue">0</span></label>
                                        <input type="range" class="form-range" min="0" max="10" step="0.5" id="filter_rating">
                                    </div>
                                    
                                    <div class="mb-3">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="sortByRating" checked>
                                            <label class="form-check-label" for="sortByRating">Sort by Rating (High to Low)</label>
                                        </div>
                                    </div>

                                    <div class="d-flex justify-content-between">
                                        <button class="btn btn-secondary prev-step" data-prev="step3">Previous</button>
                                        <button class="btn btn-success" id="applyFilters">Apply Filters</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <h2 class="card-title mb-0">Manage Sites</h2>
                                <div>
                                    <span id="filterBadges" class="me-3"></span>
                                    <button id="copyToExcel" class="btn btn-success me-2 d-none">
                                        <i class="fas fa-file-excel me-1"></i> Copy to Excel
                                    </button>
                                    @if(auth()->user()->canManageSites())
                                    <a href="#" data-bs-toggle="modal" data-bs-target="#AddSiteModal"
                                        class="btn btn-primary">
                                        <i class="fas fa-plus me-2"></i> Add New Site
                                    </a>
                                    @endif
                                </div>
                            </div>

                            <!-- No data message -->
                            <div id="noFiltersMessage" class="text-center py-5">
                                <i class="fas fa-filter fa-3x text-muted mb-3"></i>
                                <h4 class="text-muted">Please use the filters above to view sites</h4>
                                <p class="text-muted">Select options in each step to display matching sites</p>
                            </div>

                            <div id="sitesTableContainer" class="d-none">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th scope="col">Site Name</th>
                                            <th scope="col">URL</th>
                                            <th scope="col">Status</th>
                                                <th scope="col">Rating</th>
                                                <th scope="col">Categories</th>
                                                <th scope="col">Countries</th>
                                            <th scope="col">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr id="loading-spinner" style="display: none;">
                                                <td colspan="7" class="text-center">
                                                <div class="d-flex justify-content-center align-items-center py-4">
                                                    <div class="spinner-border text-primary" role="status">
                                                        <span class="visually-hidden">Loading...</span>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                            <tr id="no-results" class="d-none">
                                                <td colspan="7" class="text-center py-4">
                                                    <i class="fas fa-search fa-2x text-muted mb-3"></i>
                                                    <h5 class="text-muted">No matching sites found</h5>
                                                    <p class="text-muted">Try adjusting your filter criteria</p>
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
        </div>

        <!-- Add Modal -->
        <div class="modal fade" id="AddSiteModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">

                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="exampleModalLabel">Add Site</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                        <form id="addSiteForm" action="" method="post">
                            @csrf
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                <label for="name">Site Name</label>
                                <input type="text" class="form-control" id="name" name="name" required>
                            </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                <label for="url">Site URL</label>
                                <input type="url" class="form-control" id="url" name="url" required>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group mb-3">
                                <label for="description">Description</label>
                                <textarea class="form-control" id="description" name="description"></textarea>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-4">
                            <div class="form-group">
                                <label for="status">Status</label>
                                <select class="form-control" id="status" name="status">
                                    <option value="active" selected>Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>
                                </div>
                                <div class="col-md-4">
                            <div class="form-group">
                                <label for="type">Type</label>
                                <select class="form-control" id="type" name="type">
                                    <option value="general" selected>General</option>
                                    <option value="blog">Blog</option>
                                    <option value="shop">Shop</option>
                                    <option value="portfolio">Portfolio</option>
                                </select>
                            </div>
                                </div>
                                <div class="col-md-4">
                            <div class="form-group">
                                <label for="theme">Theme</label>
                                <input type="text" class="form-control" id="theme" name="theme" value="default">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label>Categories</label>
                                        <div class="border p-3 rounded" style="max-height: 150px; overflow-y: auto;">
                                            @foreach($categories as $category)
                                                <div class="form-check">
                                                    <input class="form-check-input add-category" type="checkbox" name="categories[]" value="{{ $category->id }}" id="category{{ $category->id }}" data-category-name="{{ $category->name }}">
                                                    <label class="form-check-label" for="category{{ $category->id }}">
                                                        {{ $category->name }}
                                                    </label>
                                                </div>
                                            @endforeach
                                        </div>
                                        <small class="text-muted">Select multiple categories</small>
                                        <div class="alert alert-info mt-2 p-2" style="font-size: 0.8rem;">
                                            <i class="fas fa-info-circle me-1"></i>
                                            Category selection affects which countries, purposes and features are compatible
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label>Work Purposes</label>
                                        <div id="selectedCategoriesBadgesAddForm" class="mb-2"></div>
                                        <div class="border p-3 rounded" style="max-height: 150px; overflow-y: auto;">
                                            @foreach($purposes as $purpose)
                                                <div class="form-check">
                                                    <input class="form-check-input add-purpose" type="checkbox" name="purposes[]" value="{{ $purpose->id }}" id="purpose{{ $purpose->id }}">
                                                    <label class="form-check-label" for="purpose{{ $purpose->id }}">
                                                        {{ $purpose->name }}
                                                    </label>
                                                </div>
                                            @endforeach
                                        </div>
                                        <small class="text-muted">Select multiple work purposes</small>
                                        <span id="purposeCompatibilityNoteAdd" class="compatibility-note d-none">
                                            Crossed-out options are not compatible with selected categories
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group mb-3">
                                <label>Countries</label>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" name="is_global" value="1" id="isGlobal">
                                    <label class="form-check-label" for="isGlobal">
                                        <strong>Global (All Countries)</strong>
                                    </label>
                                </div>
                                <div id="countriesContainer" class="border p-3 rounded" style="max-height: 150px; overflow-y: auto;">
                                    @foreach($countries as $country)
                                        <div class="form-check">
                                            <input class="form-check-input country-checkbox add-country" type="checkbox" name="countries[]" value="{{ $country->id }}" id="country{{ $country->id }}">
                                            <label class="form-check-label" for="country{{ $country->id }}">
                                                {{ $country->name }}
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                                <small class="text-muted">Select multiple countries or check Global</small>
                                <span id="countryCompatibilityNoteAdd" class="compatibility-note d-none">
                                    Crossed-out options are not compatible with selected categories
                                </span>
                            </div>

                            <div class="form-group mb-3">
                                <label>Site Features (Rating System)</label>
                                <div class="border p-3 rounded" style="max-height: 200px; overflow-y: auto;">
                                    <div class="row">
                                        @foreach($features as $feature)
                                            <div class="col-md-6 mb-2">
                                                <div class="form-check">
                                                    <input class="form-check-input feature-checkbox add-feature" type="checkbox" name="features[]" 
                                                        value="{{ $feature->id }}" id="feature{{ $feature->id }}" 
                                                        data-points="{{ $feature->points }}">
                                                    <label class="form-check-label" for="feature{{ $feature->id }}">
                                                        {{ $feature->name }} <span class="badge bg-info">{{ $feature->points }} pts</span>
                                                    </label>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                                <div class="progress mt-2">
                                    <div id="ratingProgress" class="progress-bar" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0%</div>
                                </div>
                                <small class="text-muted">Total rating: <span id="currentRating">0</span> out of <span id="maxRating">{{ $features->sum('points') }}</span> points</small>
                                <span id="featureCompatibilityNoteAdd" class="compatibility-note d-none">
                                    Crossed-out options are not compatible with selected categories
                                </span>
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
        <div class="modal fade" id="EditModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">

                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="editModalLabel">Edit Site</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                        <form id="editSiteForm" method="post">
                            @csrf
                            <input type="hidden" id="edit_id" name="edit_id">

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                <label for="edit_name">Site Name</label>
                                <input type="text" class="form-control" id="edit_name" name="edit_name" required>
                            </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                <label for="edit_url">Site URL</label>
                                <input type="url" class="form-control" id="edit_url" name="edit_url" required>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group mb-3">
                                <label for="edit_description">Description</label>
                                <textarea class="form-control" id="edit_description" name="edit_description"></textarea>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <div class="form-group">
                                <label for="edit_status">Status</label>
                                <select class="form-control" id="edit_status" name="edit_status">
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                <label for="edit_type">Type</label>
                                <select class="form-control" id="edit_type" name="edit_type">
                                    <option value="general">General</option>
                                    <option value="blog">Blog</option>
                                    <option value="shop">Shop</option>
                                    <option value="portfolio">Portfolio</option>
                                </select>
                            </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                <label for="edit_theme">Theme</label>
                                        <input type="text" class="form-control" id="edit_theme" name="edit_theme" value="default">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label>Categories</label>
                                        <div class="border p-3 rounded" style="max-height: 150px; overflow-y: auto;">
                                            @foreach($categories as $category)
                                                <div class="form-check">
                                                    <input class="form-check-input edit-category" type="checkbox" name="categories[]" value="{{ $category->id }}" id="edit_category{{ $category->id }}" data-category-name="{{ $category->name }}">
                                                    <label class="form-check-label" for="edit_category{{ $category->id }}">
                                                        {{ $category->name }}
                                                    </label>
                                                </div>
                                            @endforeach
                                        </div>
                                        <small class="text-muted">Select multiple categories</small>
                                        <div class="alert alert-info mt-2 p-2" style="font-size: 0.8rem;">
                                            <i class="fas fa-info-circle me-1"></i>
                                            Category selection affects which countries, purposes and features are compatible
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label>Work Purposes</label>
                                        <div id="selectedCategoriesBadgesEditForm" class="mb-2"></div>
                                        <div class="border p-3 rounded" style="max-height: 150px; overflow-y: auto;">
                                            @foreach($purposes as $purpose)
                                                <div class="form-check">
                                                    <input class="form-check-input edit-purpose" type="checkbox" name="purposes[]" value="{{ $purpose->id }}" id="edit_purpose{{ $purpose->id }}">
                                                    <label class="form-check-label" for="edit_purpose{{ $purpose->id }}">
                                                        {{ $purpose->name }}
                                                    </label>
                                                </div>
                                            @endforeach
                                        </div>
                                        <small class="text-muted">Select multiple work purposes</small>
                                        <span id="purposeCompatibilityNoteEdit" class="compatibility-note d-none">
                                            Crossed-out options are not compatible with selected categories
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group mb-3">
                                <label>Countries</label>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" name="is_global" value="1" id="edit_isGlobal">
                                    <label class="form-check-label" for="edit_isGlobal">
                                        <strong>Global (All Countries)</strong>
                                    </label>
                                </div>
                                <div id="edit_countriesContainer" class="border p-3 rounded" style="max-height: 150px; overflow-y: auto;">
                                    @foreach($countries as $country)
                                        <div class="form-check">
                                            <input class="form-check-input edit-country-checkbox edit-country" type="checkbox" name="countries[]" value="{{ $country->id }}" id="edit_country{{ $country->id }}">
                                            <label class="form-check-label" for="edit_country{{ $country->id }}">
                                                {{ $country->name }}
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                                <small class="text-muted">Select multiple countries or check Global</small>
                                <span id="countryCompatibilityNoteEdit" class="compatibility-note d-none">
                                    Crossed-out options are not compatible with selected categories
                                </span>
                            </div>

                            <div class="form-group mb-3">
                                <label>Site Features (Rating System)</label>
                                <div class="border p-3 rounded" style="max-height: 200px; overflow-y: auto;">
                                    <div class="row">
                                        @foreach($features as $feature)
                                            <div class="col-md-6 mb-2">
                                                <div class="form-check">
                                                    <input class="form-check-input edit-feature-checkbox edit-feature" type="checkbox" name="features[]" 
                                                        value="{{ $feature->id }}" id="edit_feature{{ $feature->id }}" 
                                                        data-points="{{ $feature->points }}">
                                                    <label class="form-check-label" for="edit_feature{{ $feature->id }}">
                                                        {{ $feature->name }} <span class="badge bg-info">{{ $feature->points }} pts</span>
                                                    </label>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                                <div class="progress mt-2">
                                    <div id="edit_ratingProgress" class="progress-bar" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0%</div>
                                </div>
                                <small class="text-muted">Total rating: <span id="edit_currentRating">0</span> out of <span id="edit_maxRating">{{ $features->sum('points') }}</span> points</small>
                                <span id="featureCompatibilityNoteEdit" class="compatibility-note d-none">
                                    Crossed-out options are not compatible with selected categories
                                </span>
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
                // Filter step navigation
                $('.next-step').on('click', function() {
                    const currentStep = $(this).closest('.filter-step');
                    const nextStepId = $(this).data('next');
                    
                    currentStep.removeClass('active').addClass('d-none');
                    $('#' + nextStepId).removeClass('d-none').addClass('active');
                    
                    // If moving to step 2 (countries), update selected categories badges
                    if (nextStepId === 'step2') {
                        updateSelectedCategoriesBadges();
                        filterCountriesByCategories();
                    }
                    
                    // If moving to step 3 (purposes), show selected categories
                    if (nextStepId === 'step3') {
                        const badgesHtml = $('#selectedCategoriesBadges').html();
                        $('#selectedCategoriesBadges2').html(badgesHtml);
                        filterPurposesByCategories();
                    }
                    
                    // If moving to step 4 (features), show selected categories
                    if (nextStepId === 'step4') {
                        const badgesHtml = $('#selectedCategoriesBadges').html();
                        $('#selectedCategoriesBadges3').html(badgesHtml);
                        filterFeaturesByCategories();
                    }
                });

                $('.prev-step').on('click', function() {
                    const currentStep = $(this).closest('.filter-step');
                    const prevStepId = $(this).data('prev');
                    
                    currentStep.removeClass('active').addClass('d-none');
                    $('#' + prevStepId).removeClass('d-none').addClass('active');
                });

                // Rating slider
                $('#filter_rating').on('input', function() {
                    $('#ratingValue').text($(this).val());
                });

                // Handle global checkbox for countries - modified to NOT disable country selection
                $('#filter_global').on('change', function() {
                    // Visual indicator that global is selected, but allow country selection
                    if ($(this).is(':checked')) {
                        $('#filter_countriesContainer').addClass('border-primary');
                    } else {
                        $('#filter_countriesContainer').removeClass('border-primary');
                    }
                    // Countries remain selectable either way
                });

                // Reset filters
                $('#resetFilters').on('click', function() {
                    // Reset all checkboxes
                    $('.filter-category, .filter-country, .filter-purpose, #filter_global').prop('checked', false);
                    $('.filter-country').prop('disabled', false);
                    $('#filter_countriesContainer').removeClass('opacity-50 border-primary');
                    
                    // Reset rating slider
                    $('#filter_rating').val(0);
                    $('#ratingValue').text('0');
                    
                    // Set sort option to default
                    $('#sortByRating').prop('checked', true);
                    
                    // Hide sites table and show no filters message
                    $('#sitesTableContainer').addClass('d-none');
                    $('#noFiltersMessage').removeClass('d-none');
                    $('#no-results').addClass('d-none');
                    
                    // Hide Excel button
                    $('#copyToExcel').addClass('d-none');
                    
                    // Clear filter badges
                    updateFilterBadges();
                    
                    // Reset to first step
                    $('.filter-step').removeClass('active').addClass('d-none');
                    $('#step1').removeClass('d-none').addClass('active');
                });

                // Apply filters
                $('#applyFilters').on('click', function() {
                    const selectedCategories = [];
                    $('.filter-category:checked').each(function() {
                        selectedCategories.push($(this).val());
                    });
                    
                    const isGlobal = $('#filter_global').is(':checked');
                    
                    const selectedCountries = [];
                    $('.filter-country:checked').each(function() {
                        selectedCountries.push($(this).val());
                    });
                    
                    const selectedPurposes = [];
                    $('.filter-purpose:checked').each(function() {
                        selectedPurposes.push($(this).val());
                    });
                    
                    const minRating = parseFloat($('#filter_rating').val());
                    const sortByRating = $('#sortByRating').is(':checked');
                    
                    // Update filter badges
                    updateFilterBadges(selectedCategories, isGlobal, selectedCountries, selectedPurposes, minRating);
                    
                    // Fetch filtered sites
                    fetchFilteredSites(selectedCategories, isGlobal, selectedCountries, selectedPurposes, minRating, sortByRating);
                });
                
                // Copy to Excel functionality
                $('#copyToExcel').on('click', function() {
                    const tableData = [];
                    
                    // Get headers
                    const headers = [];
                    $('#sitesTableContainer table thead th').each(function() {
                        headers.push($(this).text());
                    });
                    
                    // Remove the Actions column header
                    headers.pop();
                    
                    tableData.push(headers);
                    
                    // Get rows data
                    $('#sitesTableContainer table tbody tr:not(#loading-spinner):not(#no-results)').each(function() {
                        const rowData = [];
                        $(this).find('td:not(:last-child)').each(function() {
                            // Get text content, removing any HTML
                            rowData.push($(this).text().trim());
                        });
                        
                        if (rowData.length > 0) {
                            tableData.push(rowData);
                        }
                    });
                    
                    // Convert to Excel format (tab-separated values)
                    let excelContent = '';
                    tableData.forEach(row => {
                        excelContent += row.join('\t') + '\n';
                    });
                    
                    // Copy to clipboard
                    copyToClipboard(excelContent);
                    
                    // Show feedback
                    const originalText = $(this).html();
                    $(this).html('<i class="fas fa-check me-1"></i> Copied!');
                    
                    setTimeout(() => {
                        $(this).html(originalText);
                    }, 2000);
                });
                
                // Function to copy to clipboard
                function copyToClipboard(text) {
                    const textarea = document.createElement('textarea');
                    textarea.value = text;
                    document.body.appendChild(textarea);
                    textarea.select();
                    document.execCommand('copy');
                    document.body.removeChild(textarea);
                }

                // Update filter badges
                function updateFilterBadges(categories = [], isGlobal = false, countries = [], purposes = [], rating = 0) {
                    const $badges = $('#filterBadges');
                    $badges.empty();
                    
                    if (categories.length === 0 && countries.length === 0 && purposes.length === 0 && rating === 0) {
                        return;
                    }
                    
                    if (categories.length > 0) {
                        $badges.append(`<span class="badge bg-primary me-1">Categories: ${categories.length}</span>`);
                    }
                    
                    if (isGlobal) {
                        $badges.append(`<span class="badge bg-info me-1">Global</span>`);
                    } else if (countries.length > 0) {
                        $badges.append(`<span class="badge bg-info me-1">Countries: ${countries.length}</span>`);
                    }
                    
                    if (purposes.length > 0) {
                        $badges.append(`<span class="badge bg-secondary me-1">Purposes: ${purposes.length}</span>`);
                    }
                    
                    if (rating > 0) {
                        $badges.append(`<span class="badge bg-success me-1">Min Rating: ${rating}</span>`);
                    }
                }

                // Show loading spinner
                function showLoading() {
                    $('tbody tr:not(#loading-spinner, #no-results)').hide();
                    $('#loading-spinner').show();
                    $('#no-results').addClass('d-none');
                }

                function hideLoading() {
                    $('#loading-spinner').hide();
                }

                // Fetch filtered sites
                function fetchFilteredSites(categories, isGlobal, countries, purposes, minRating, sortByRating) {
                    // Show sites table and hide no filters message
                    $('#noFiltersMessage').addClass('d-none');
                    $('#sitesTableContainer').removeClass('d-none');
                    $('#no-results').addClass('d-none'); // Hide no results message at start
                    
                    showLoading();
                    
                    // Debug info in console
                    console.log('Fetching sites with filters:', {
                        categories: categories,
                        is_global: isGlobal,
                        countries: countries,
                        purposes: purposes,
                        min_rating: minRating,
                        sort_by_rating: sortByRating
                    });
                    
                    // Create a debug message to show on the page
                    const debugInfoHtml = `
                        <div id="debug-info" class="alert alert-info mb-3">
                            <small>
                                <strong>Debug Info:</strong><br>
                                Categories: ${categories.length ? categories.join(', ') : 'None'}<br>
                                Global: ${isGlobal ? 'Yes' : 'No'}<br>
                                Countries: ${countries.length ? countries.join(', ') : 'None'}<br>
                                Purposes: ${purposes.length ? purposes.join(', ') : 'None'}<br>
                                Min Rating: ${minRating}<br>
                                Sort by Rating: ${sortByRating ? 'Yes' : 'No'}
                            </small>
                            <button class="btn-close btn-sm float-end" onclick="$('#debug-info').remove()"></button>
                        </div>
                    `;
                    
                    // Show debug info at the top of the sites table
                    $('#sitesTableContainer').prepend(debugInfoHtml);
                    
                    $.ajax({
                        type: "GET",
                        url: "{{ route('sites.fetch') }}",
                        data: {
                            categories: categories,
                            is_global: isGlobal ? 1 : 0,
                            countries: countries,
                            purposes: purposes,
                            min_rating: minRating,
                            sort_by_rating: sortByRating ? 1 : 0
                        },
                        dataType: "json",
                        success: function(response) {
                            console.log('Response received:', response); // Debug log
                            
                            // Remove existing site rows
                            $('tbody tr:not(#loading-spinner, #no-results)').remove();
                            
                            if (!response.sites || response.sites.length === 0) {
                                hideLoading();
                                $('#no-results').removeClass('d-none');
                                $('#copyToExcel').addClass('d-none');
                                
                                // Add message about no results matching the filters
                                $('#no-results td').html(`
                                    <div class="text-center py-4">
                                        <i class="fas fa-search fa-2x text-muted mb-3"></i>
                                        <h5 class="text-muted">No matching sites found</h5>
                                        <p class="text-muted">Try adjusting your filter criteria</p>
                                    </div>
                                `);
                                return;
                            }
                            
                            // Show Excel button when we have results
                            $('#copyToExcel').removeClass('d-none');
                            
                            $.each(response.sites, function(index, site) {
                                // Add a color class based on rating
                                let ratingClass = '';
                                if (site.rating >= 7) {
                                    ratingClass = 'text-success fw-bold';
                                } else if (site.rating >= 4) {
                                    ratingClass = 'text-warning';
                                } else {
                                    ratingClass = 'text-danger';
                                }
                                
                                // Safely handle null or undefined values
                                const siteName = site.name || 'Unnamed';
                                const siteUrl = site.url || '#';
                                const siteStatus = site.status || 'unknown';
                                const siteRating = typeof site.rating === 'number' ? site.rating.toFixed(1) : '0.0';
                                const siteMaxRating = site.max_rating || 10;
                                const categoriesList = site.categories_list || 'None';
                                const countriesList = site.countries_list || 'None';
                                
                                $('tbody').append(`
                                    <tr>
                                        <td>${siteName}</td>
                                        <td>${siteUrl}</td>
                                        <td>${siteStatus}</td>
                                        <td class="${ratingClass}">${siteRating} / ${siteMaxRating}</td>
                                        <td>${categoriesList}</td>
                                        <td>${countriesList}</td>
                                        <td>
                                            <div class="btn-group" role="group" aria-label="Basic example">
                                                @if(auth()->user()->canManageSites())
                                                <button type="button" value="${site.id}" class="btn btn-primary edit-btn"><i class='bx bxs-edit'></i></button>
                                                <button type="button" value="${site.id}" class="btn btn-danger delete-btn"><i class='bx bxs-trash'></i></button>
                                                @else
                                                <button type="button" class="btn btn-secondary" disabled><i class='bx bxs-lock'></i></button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                `);
                            });
                            hideLoading();
                        },
                        error: function(xhr, status, error) {
                            console.error("Error fetching sites:", xhr.responseText, status, error);
                            hideLoading();
                            $('#no-results').removeClass('d-none');
                            
                            // Show detailed error in the no-results element
                            let errorMessage = 'Failed to load sites';
                            let detailMessage = '';
                            
                            try {
                                const responseObj = JSON.parse(xhr.responseText);
                                if (responseObj && responseObj.message) {
                                    detailMessage = responseObj.message;
                                }
                            } catch(e) {
                                detailMessage = `${error}: ${xhr.responseText || 'Unknown error'}`;
                            }
                            
                            // Display the error in the no-results area
                            $('#no-results td').html(`
                                <div class="text-center py-4">
                                    <i class="fas fa-exclamation-triangle fa-2x text-danger mb-3"></i>
                                    <h5 class="text-danger">Error Loading Sites</h5>
                                    <p class="text-muted">${detailMessage}</p>
                                    <button class="btn btn-sm btn-outline-primary mt-2" onclick="$('#resetFilters').click()">
                                        Reset Filters
                                    </button>
                                </div>
                            `);
                            
                            Swal.fire({
                                title: 'Error!',
                                text: errorMessage + '. ' + detailMessage,
                                icon: 'error',
                                allowOutsideClick: true,
                                showConfirmButton: true
                            });
                        },
                        timeout: 20000 // 20 second timeout to prevent infinite loading
                    });
                }

                // Handle global checkbox for countries in add form - modified to NOT disable countries
                $('#isGlobal').on('change', function() {
                    // Visual indicator that global is selected, but allow country selection
                    if ($(this).is(':checked')) {
                        $('#countriesContainer').addClass('border-primary');
                    } else {
                        $('#countriesContainer').removeClass('border-primary');
                    }
                    // Countries remain selectable either way
                });

                // Handle global checkbox for countries in edit modal - modified to NOT disable countries
                $('#edit_isGlobal').on('change', function() {
                    // Visual indicator that global is selected, but allow country selection
                    if ($(this).is(':checked')) {
                        $('#edit_countriesContainer').addClass('border-primary');
                    } else {
                        $('#edit_countriesContainer').removeClass('border-primary');
                    }
                    // Countries remain selectable either way
                });

                // Calculate rating for add form
                $('.feature-checkbox').on('change', function() {
                    updateRating();
                });

                // Calculate rating for edit form
                $('.edit-feature-checkbox').on('change', function() {
                    updateEditRating();
                });

                // Category compatibility filtering in Add Site modal
                $('.add-category').on('change', function() {
                    updateSelectedCategoriesBadgesAddForm();
                    filterAddFormOptionsByCategories();
                });
                
                // Update selected categories badges in Add form
                function updateSelectedCategoriesBadgesAddForm() {
                    const $badgesContainer = $('#selectedCategoriesBadgesAddForm');
                    $badgesContainer.empty();
                    
                    if ($('.add-category:checked').length === 0) {
                        // Reset all filters if no categories selected
                        $('.add-country, .add-purpose, .add-feature').closest('.form-check').removeClass('unsupported-option');
                        $('#countryCompatibilityNoteAdd, #purposeCompatibilityNoteAdd, #featureCompatibilityNoteAdd').addClass('d-none');
                        return;
                    }
                    
                    $badgesContainer.append('<small class="text-muted d-block mb-1">Compatible with:</small>');
                    
                    $('.add-category:checked').each(function() {
                        const categoryName = $(this).data('category-name');
                        const categoryId = $(this).val();
                        $badgesContainer.append(`
                            <span class="badge bg-primary category-badge" data-category-id="${categoryId}">
                                ${categoryName}
                            </span>
                        `);
                    });
                }
                
                // Filter Add form options based on selected categories
                function filterAddFormOptionsByCategories() {
                    const selectedCategories = [];
                    $('.add-category:checked').each(function() {
                        selectedCategories.push($(this).val());
                    });
                    
                    if (selectedCategories.length === 0) {
                        // Reset filters if no categories selected
                        $('.add-country, .add-purpose, .add-feature').closest('.form-check').removeClass('unsupported-option');
                        $('#countryCompatibilityNoteAdd, #purposeCompatibilityNoteAdd, #featureCompatibilityNoteAdd').addClass('d-none');
                        return;
                    }
                    
                    // Show compatibility notes
                    $('#countryCompatibilityNoteAdd, #purposeCompatibilityNoteAdd, #featureCompatibilityNoteAdd').removeClass('d-none');
                    
                    // Show loading indicator
                    const loadingHtml = '<div class="text-center my-2"><div class="spinner-border spinner-border-sm text-primary" role="status"></div> <small class="text-muted">Loading compatible options...</small></div>';
                    $('#selectedCategoriesBadgesAddForm').append(loadingHtml);
                    
                    // Add debug info to console
                    console.log('Fetching compatible options for categories:', selectedCategories);
                    
                    // Batch all requests into a single AJAX call to improve performance
                    $.ajax({
                        type: "GET",
                        url: "{{ route('sites.compatible-options') }}",
                        data: {
                            categories: selectedCategories,
                            option_types: ['countries', 'purposes', 'features']
                        },
                        dataType: "json",
                        success: function(response) {
                            // Remove loading indicator
                            $('#selectedCategoriesBadgesAddForm .spinner-border').parent().remove();
                            
                            console.log('Compatible options response:', response);
                            
                            if (!response.success) {
                                // Show error message
                                const errorMsg = $('<div class="alert alert-danger py-1 px-2 mt-1" style="font-size: 0.8rem;"><i class="fas fa-exclamation-triangle"></i> Error loading compatibility data</div>');
                                $('#selectedCategoriesBadgesAddForm').append(errorMsg);
                                
                                // If error, show all options rather than none
                                $('.add-country, .add-purpose, .add-feature').closest('.form-check').removeClass('unsupported-option');
                                
                                setTimeout(() => {
                                    errorMsg.fadeOut(300, function() { $(this).remove(); });
                                }, 3000);
                                
                                return;
                            }
                            
                            // Reset all checkboxes
                            $('.add-country, .add-purpose, .add-feature').prop('checked', false).closest('.form-check').addClass('unsupported-option');
                            
                            // Process countries
                            if (response.countries && response.countries.length > 0) {
                                response.countries.forEach(function(countryId) {
                                    $(`#country${countryId}`).closest('.form-check').removeClass('unsupported-option');
                                });
                            }
                            
                            // Process work purposes
                            if (response.purposes && response.purposes.length > 0) {
                                response.purposes.forEach(function(purposeId) {
                                    $(`#purpose${purposeId}`).closest('.form-check').removeClass('unsupported-option');
                                });
                            }
                            
                            // Process features
                            if (response.features && response.features.length > 0) {
                                response.features.forEach(function(featureId) {
                                    $(`#feature${featureId}`).closest('.form-check').removeClass('unsupported-option');
                                });
                            }
                            
                            // Update rating after features change
                            updateRating();
                        },
                        error: function(xhr, status, error) {
                            // Remove loading indicator
                            $('#selectedCategoriesBadgesAddForm .spinner-border').parent().remove();
                            
                            console.error('Compatible options error:', error, xhr.responseText);
                            
                            // Show error message that disappears after 3 seconds
                            const errorMsg = $('<div class="alert alert-danger py-1 px-2 mt-1 mb-0" style="font-size: 0.8rem;"><i class="fas fa-exclamation-triangle"></i> Error loading compatibility data</div>');
                            $('#selectedCategoriesBadgesAddForm').append(errorMsg);
                            
                            // If error, show all options rather than none
                            $('.add-country, .add-purpose, .add-feature').closest('.form-check').removeClass('unsupported-option');
                            
                            setTimeout(() => {
                                errorMsg.fadeOut(300, function() { $(this).remove(); });
                            }, 3000);
                        },
                        timeout: 15000 // 15 second timeout to prevent infinite loading
                    });
                }

                // Function to update the rating progress bar and values
                function updateRating() {
                    let totalPoints = 0;
                    let maxPoints = parseInt($('#maxRating').text());
                    
                    $('.feature-checkbox:checked').each(function() {
                        totalPoints += parseInt($(this).data('points'));
                    });
                    
                    const percentage = (totalPoints / maxPoints) * 100;
                    $('#currentRating').text(totalPoints);
                    $('#ratingProgress').css('width', percentage + '%').attr('aria-valuenow', percentage).text(Math.round(percentage) + '%');
                    
                    // Change progress bar color based on rating
                    if (percentage < 33) {
                        $('#ratingProgress').removeClass('bg-warning bg-success').addClass('bg-danger');
                    } else if (percentage < 66) {
                        $('#ratingProgress').removeClass('bg-danger bg-success').addClass('bg-warning');
                    } else {
                        $('#ratingProgress').removeClass('bg-danger bg-warning').addClass('bg-success');
                    }
                }

                // Function to update the rating progress bar and values for edit form
                function updateEditRating() {
                    let totalPoints = 0;
                    let maxPoints = parseInt($('#edit_maxRating').text());
                    
                    $('.edit-feature-checkbox:checked').each(function() {
                        totalPoints += parseInt($(this).data('points'));
                    });
                    
                    const percentage = (totalPoints / maxPoints) * 100;
                    $('#edit_currentRating').text(totalPoints);
                    $('#edit_ratingProgress').css('width', percentage + '%').attr('aria-valuenow', percentage).text(Math.round(percentage) + '%');
                    
                    // Change progress bar color based on rating
                    if (percentage < 33) {
                        $('#edit_ratingProgress').removeClass('bg-warning bg-success').addClass('bg-danger');
                    } else if (percentage < 66) {
                        $('#edit_ratingProgress').removeClass('bg-danger bg-success').addClass('bg-warning');
                    } else {
                        $('#edit_ratingProgress').removeClass('bg-danger bg-warning').addClass('bg-success');
                    }
                }

                // Reset forms when closing modal
                $('#AddSiteModal').on('hidden.bs.modal', function () {
                    $('#addSiteForm')[0].reset();
                    $('.country-checkbox').prop('disabled', false);
                    $('#countriesContainer').removeClass('opacity-50');
                    updateRating();
                    
                    // Reset compatibility filtering
                    $('.add-country, .add-purpose, .add-feature').closest('.form-check').removeClass('unsupported-option');
                    $('#selectedCategoriesBadgesAddForm').empty();
                    $('#countryCompatibilityNoteAdd, #purposeCompatibilityNoteAdd, #featureCompatibilityNoteAdd').addClass('d-none');
                });

                $('#EditModal').on('hidden.bs.modal', function () {
                    $('#edit_countriesContainer').removeClass('opacity-50');
                    $('.edit-country-checkbox').prop('disabled', false);
                    
                    // Reset compatibility filtering
                    $('.edit-country, .edit-purpose, .edit-feature').closest('.form-check').removeClass('unsupported-option');
                    $('#selectedCategoriesBadgesEditForm').empty();
                    $('#countryCompatibilityNoteEdit, #purposeCompatibilityNoteEdit, #featureCompatibilityNoteEdit').addClass('d-none');
                });
                
                // When edit modal is shown, apply compatibility filtering based on selected categories
                $('#EditModal').on('shown.bs.modal', function () {
                    updateSelectedCategoriesBadgesEditForm();
                    if ($('.edit-category:checked').length > 0) {
                        filterEditFormOptionsByCategories();
                    }
                });
                
                // Category compatibility filtering in Edit Site modal
                $('.edit-category').on('change', function() {
                    updateSelectedCategoriesBadgesEditForm();
                    filterEditFormOptionsByCategories();
                });
                
                // Update selected categories badges in Edit form
                function updateSelectedCategoriesBadgesEditForm() {
                    const $badgesContainer = $('#selectedCategoriesBadgesEditForm');
                    $badgesContainer.empty();
                    
                    if ($('.edit-category:checked').length === 0) {
                        // Reset all filters if no categories selected
                        $('.edit-country, .edit-purpose, .edit-feature').closest('.form-check').removeClass('unsupported-option');
                        $('#countryCompatibilityNoteEdit, #purposeCompatibilityNoteEdit, #featureCompatibilityNoteEdit').addClass('d-none');
                        return;
                    }
                    
                    $badgesContainer.append('<small class="text-muted d-block mb-1">Compatible with:</small>');
                    
                    $('.edit-category:checked').each(function() {
                        const categoryName = $(this).data('category-name');
                        const categoryId = $(this).val();
                        $badgesContainer.append(`
                            <span class="badge bg-primary category-badge" data-category-id="${categoryId}">
                                ${categoryName}
                            </span>
                        `);
                    });
                }
                
                // Filter Edit form options based on selected categories
                function filterEditFormOptionsByCategories() {
                    const selectedCategories = [];
                    $('.edit-category:checked').each(function() {
                        selectedCategories.push($(this).val());
                    });
                    
                    if (selectedCategories.length === 0) {
                        // Reset filters if no categories selected
                        $('.edit-country, .edit-purpose, .edit-feature').closest('.form-check').removeClass('unsupported-option');
                        $('#countryCompatibilityNoteEdit, #purposeCompatibilityNoteEdit, #featureCompatibilityNoteEdit').addClass('d-none');
                        return;
                    }
                    
                    // Show compatibility notes
                    $('#countryCompatibilityNoteEdit, #purposeCompatibilityNoteEdit, #featureCompatibilityNoteEdit').removeClass('d-none');
                    
                    // Show loading indicator
                    const loadingHtml = '<div class="text-center my-2"><div class="spinner-border spinner-border-sm text-primary" role="status"></div> <small class="text-muted">Loading compatible options...</small></div>';
                    $('#selectedCategoriesBadgesEditForm').append(loadingHtml);
                    
                    // Batch all requests into a single AJAX call to improve performance
                    $.ajax({
                        type: "GET",
                        url: "{{ route('sites.compatible-options') }}",
                        data: {
                            categories: selectedCategories,
                            option_types: ['countries', 'purposes', 'features']
                        },
                        dataType: "json",
                        success: function(response) {
                            // Remove loading indicator
                            $('#selectedCategoriesBadgesEditForm .spinner-border').parent().remove();
                            
                            console.log('Compatible options response:', response);
                            
                            if (!response.success) {
                                // Show error message
                                const errorMsg = $('<div class="alert alert-danger py-1 px-2 mt-1" style="font-size: 0.8rem;"><i class="fas fa-exclamation-triangle"></i> Error loading compatibility data</div>');
                                $('#selectedCategoriesBadgesEditForm').append(errorMsg);
                                
                                // If error, show all options rather than none
                                $('.edit-country, .edit-purpose, .edit-feature').closest('.form-check').removeClass('unsupported-option');
                                
                                setTimeout(() => {
                                    errorMsg.fadeOut(300, function() { $(this).remove(); });
                                }, 3000);
                                
                                return;
                            }
                            
                            // Reset all checkboxes
                            $('.edit-country, .edit-purpose, .edit-feature').prop('checked', false).closest('.form-check').addClass('unsupported-option');
                            
                            // Process countries
                            if (response.countries && response.countries.length > 0) {
                                response.countries.forEach(function(countryId) {
                                    $(`#edit_country${countryId}`).closest('.form-check').removeClass('unsupported-option');
                                });
                            }
                            
                            // Process work purposes
                            if (response.purposes && response.purposes.length > 0) {
                                response.purposes.forEach(function(purposeId) {
                                    $(`#edit_purpose${purposeId}`).closest('.form-check').removeClass('unsupported-option');
                                });
                            }
                            
                            // Process features
                            if (response.features && response.features.length > 0) {
                                response.features.forEach(function(featureId) {
                                    $(`#edit_feature${featureId}`).closest('.form-check').removeClass('unsupported-option');
                                });
                            }
                            
                            // Uncheck incompatible selected options
                            $('.edit-country:checked, .edit-purpose:checked, .edit-feature:checked').each(function() {
                                if ($(this).closest('.form-check').hasClass('unsupported-option')) {
                                    $(this).prop('checked', false);
                                }
                            });
                            
                            // Update rating after features change
                            updateEditRating();
                        },
                        error: function(xhr, status, error) {
                            // Remove loading indicator
                            $('#selectedCategoriesBadgesEditForm .spinner-border').parent().remove();
                            
                            console.error('Compatible options error:', error, xhr.responseText);
                            
                            // Show error message that disappears after 3 seconds
                            const errorMsg = $('<div class="alert alert-danger py-1 px-2 mt-1 mb-0" style="font-size: 0.8rem;"><i class="fas fa-exclamation-triangle"></i> Error loading compatibility data</div>');
                            $('#selectedCategoriesBadgesEditForm').append(errorMsg);
                            
                            // If error, show all options rather than none
                            $('.edit-country, .edit-purpose, .edit-feature').closest('.form-check').removeClass('unsupported-option');
                            
                            setTimeout(() => {
                                errorMsg.fadeOut(300, function() { $(this).remove(); });
                            }, 3000);
                        },
                        timeout: 15000 // 15 second timeout to prevent infinite loading
                    });
                }

                // Add loading state to delete action
                $('tbody').on('click', '.delete-btn', function(e) {
                    e.preventDefault();
                    var siteId = $(this).val();
                    var deleteButton = $(this);

                    Swal.fire({
                        title: 'Are you sure?',
                        text: "You won't be able to revert this!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Yes, delete it!'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // Disable the delete button and show loading state
                            deleteButton.prop('disabled', true).html(
                                '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>'
                                );

                            $.ajax({
                                type: "DELETE",
                                url: "{{ route('sites.destroy', '') }}/" + siteId,
                                data: {
                                    '_token': $('meta[name="csrf-token"]').attr('content')
                                },
                                success: function(response) {
                                    deleteButton.prop('disabled', false).html(
                                        '<i class="bx bxs-trash"></i>');
                                    if (response.status === 200) {
                                        Swal.fire({
                                            title: 'Deleted!',
                                            text: response.message ||
                                                'Site has been deleted.',
                                            icon: 'success',
                                            allowOutsideClick: true,
                                            showConfirmButton: true,
                                            didOpen: () => {
                                                const popup = Swal.getPopup();
                                                popup.setAttribute('draggable',
                                                    'true');
                                            }
                                        }).then(() => {
                                            fetchSites();
                                        });
                                    } else {
                                        Swal.fire({
                                            title: 'Error!',
                                            text: response.message ||
                                                'Failed to delete site.',
                                            icon: 'error',
                                            allowOutsideClick: true,
                                            showConfirmButton: true,
                                            didOpen: () => {
                                                const popup = Swal.getPopup();
                                                popup.setAttribute('draggable',
                                                    'true');
                                            }
                                        });
                                    }
                                },
                                error: function(xhr) {
                                    deleteButton.prop('disabled', false).html(
                                        '<i class="bx bxs-trash"></i>');
                                    Swal.fire({
                                        title: 'Error!',
                                        text: xhr.responseJSON?.message ||
                                            'Something went wrong!',
                                        icon: 'error',
                                        allowOutsideClick: true,
                                        showConfirmButton: true,
                                        didOpen: () => {
                                            const popup = Swal.getPopup();
                                            popup.setAttribute('draggable',
                                                'true');
                                        }
                                    });
                                }
                            });
                        }
                    });
                });

                // Add loading state to edit action
                $('tbody').on('click', '.edit-btn', function(e) {
                    e.preventDefault();
                    var siteId = $(this).val();
                    var editButton = $(this);

                    // Disable the edit button and show loading state
                    editButton.prop('disabled', true).html(
                        '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>'
                        );

                    $.ajax({
                        type: "GET",
                        url: "{{ route('sites.edit', '') }}/" + siteId,
                        success: function(response) {
                            editButton.prop('disabled', false).html('<i class="bx bxs-edit"></i>');
                            if (response.status === 200) {
                                $('#EditModal').modal('show');
                                
                                // Populate basic fields
                                $('#edit_id').val(response.site.id);
                                $('#edit_name').val(response.site.name);
                                $('#edit_url').val(response.site.url);
                                $('#edit_description').val(response.site.description);
                                $('#edit_status').val(response.site.status);
                                $('#edit_type').val(response.site.type);
                                $('#edit_theme').val(response.site.theme);
                                
                                // Reset all checkboxes first
                                $('input[name="categories[]"]').prop('checked', false);
                                $('input[name="countries[]"]').prop('checked', false);
                                $('input[name="purposes[]"]').prop('checked', false);
                                $('input[name="features[]"]').prop('checked', false);
                                
                                // Set categories
                                if (response.site_categories) {
                                    response.site_categories.forEach(function(categoryId) {
                                        $('#edit_category' + categoryId).prop('checked', true);
                                    });
                                }
                                
                                // Set countries and global flag
                                if (response.is_global) {
                                    $('#edit_isGlobal').prop('checked', true);
                                    $('.edit-country-checkbox').prop('disabled', true);
                                    $('#edit_countriesContainer').addClass('opacity-50');
                                } else {
                                    $('#edit_isGlobal').prop('checked', false);
                                    $('.edit-country-checkbox').prop('disabled', false);
                                    $('#edit_countriesContainer').removeClass('opacity-50');
                                    
                                    if (response.site_countries) {
                                        response.site_countries.forEach(function(countryId) {
                                            $('#edit_country' + countryId).prop('checked', true);
                                        });
                                    }
                                }
                                
                                // Set work purposes
                                if (response.site_purposes) {
                                    response.site_purposes.forEach(function(purposeId) {
                                        $('#edit_purpose' + purposeId).prop('checked', true);
                                    });
                                }
                                
                                // Set features
                                if (response.site_features) {
                                    response.site_features.forEach(function(featureId) {
                                        $('#edit_feature' + featureId).prop('checked', true);
                                    });
                                    updateEditRating();
                                }
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
                        },
                        error: function(xhr) {
                            editButton.prop('disabled', false).html('<i class="bx bxs-edit"></i>');
                            Swal.fire({
                                title: "Error!",
                                text: "Failed to load site details.",
                                icon: "error",
                                allowOutsideClick: true,
                                showConfirmButton: true
                            });
                        }
                    });
                });

                $('.edit_btn').on('click', function(e) {
                    e.preventDefault();

                    // Collect selected categories, countries, purposes, and features
                    const selectedCategories = [];
                    $('input[name="categories[]"]:checked').each(function() {
                        selectedCategories.push($(this).val());
                    });
                    
                    const selectedCountries = [];
                    $('input[name="countries[]"]:checked').each(function() {
                        selectedCountries.push($(this).val());
                    });
                    
                    const selectedPurposes = [];
                    $('input[name="purposes[]"]:checked').each(function() {
                        selectedPurposes.push($(this).val());
                    });
                    
                    const selectedFeatures = [];
                    $('input[name="features[]"]:checked').each(function() {
                        selectedFeatures.push($(this).val());
                    });

                    const formData = {
                        id: $('#edit_id').val(),
                        name: $('#edit_name').val(),
                        url: $('#edit_url').val(),
                        description: $('#edit_description').val(),
                        status: $('#edit_status').val(),
                        type: $('#edit_type').val(),
                        theme: $('#edit_theme').val(),
                        categories: selectedCategories,
                        is_global: $('#edit_isGlobal').is(':checked') ? 1 : 0,
                        countries: selectedCountries,
                        purposes: selectedPurposes,
                        features: selectedFeatures,
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
                            } else if (response.status === 200) {
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

                    // Collect selected categories, countries, purposes, and features
                    const selectedCategories = [];
                    $('input[name="categories[]"]:checked').each(function() {
                        selectedCategories.push($(this).val());
                    });
                    
                    const selectedCountries = [];
                    $('input[name="countries[]"]:checked').each(function() {
                        selectedCountries.push($(this).val());
                    });
                    
                    const selectedPurposes = [];
                    $('input[name="purposes[]"]:checked').each(function() {
                        selectedPurposes.push($(this).val());
                    });
                    
                    const selectedFeatures = [];
                    $('input[name="features[]"]:checked').each(function() {
                        selectedFeatures.push($(this).val());
                    });

                    const formData = {
                        name: $('#name').val(),
                        url: $('#url').val(),
                        description: $('#description').val(),
                        status: $('#status').val(),
                        type: $('#type').val(),
                        theme: $('#theme').val(),
                        categories: selectedCategories,
                        is_global: $('#isGlobal').is(':checked') ? 1 : 0,
                        countries: selectedCountries,
                        purposes: selectedPurposes,
                        features: selectedFeatures,
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

                // Update selected categories badges
                function updateSelectedCategoriesBadges() {
                    const $badgesContainer = $('#selectedCategoriesBadges');
                    $badgesContainer.empty();
                    
                    if ($('.filter-category:checked').length === 0) {
                        $badgesContainer.append('<div class="alert alert-warning">No categories selected. All options will be shown.</div>');
                        return;
                    }
                    
                    $badgesContainer.append('<label class="mb-2">Filtering options for categories:</label><br>');
                    
                    $('.filter-category:checked').each(function() {
                        const categoryName = $(this).data('category-name');
                        const categoryId = $(this).val();
                        $badgesContainer.append(`
                            <span class="badge bg-primary category-badge" data-category-id="${categoryId}">
                                ${categoryName}
                            </span>
                        `);
                    });
                }
                
                // Filter countries based on selected categories
                function filterCountriesByCategories() {
                    const selectedCategories = [];
                    $('.filter-category:checked').each(function() {
                        selectedCategories.push($(this).val());
                    });
                    
                    if (selectedCategories.length === 0) {
                        // If no categories selected, show all countries
                        $('.filter-country').closest('.form-check').removeClass('unsupported-option');
                        return;
                    }
                    
                    // Get compatible countries via AJAX
                    $.ajax({
                        type: "GET",
                        url: "{{ route('sites.compatible-options') }}",
                        data: {
                            categories: selectedCategories,
                            option_type: 'countries'
                        },
                        dataType: "json",
                        success: function(response) {
                            // Reset all countries
                            $('.filter-country').prop('checked', false).closest('.form-check').addClass('unsupported-option');
                            
                            // Enable compatible countries
                            if (response.compatible_options) {
                                response.compatible_options.forEach(function(countryId) {
                                    $(`#filter_country${countryId}`).closest('.form-check').removeClass('unsupported-option');
                                });
                            }
                            
                            // Add note about compatibility
                            if (!$('#countryCompatibilityNote').length) {
                                $('#filter_countriesContainer').after(
                                    '<span id="countryCompatibilityNote" class="compatibility-note">' +
                                    'Crossed-out options are not compatible with selected categories</span>'
                                );
                            }
                        },
                        error: function() {
                            // If error, show all countries
                            $('.filter-country').closest('.form-check').removeClass('unsupported-option');
                        }
                    });
                }
                
                // Filter work purposes based on selected categories
                function filterPurposesByCategories() {
                    const selectedCategories = [];
                    $('.filter-category:checked').each(function() {
                        selectedCategories.push($(this).val());
                    });
                    
                    if (selectedCategories.length === 0) {
                        // If no categories selected, show all purposes
                        $('.filter-purpose').closest('.form-check').removeClass('unsupported-option');
                        return;
                    }
                    
                    // Get compatible purposes via AJAX
                    $.ajax({
                        type: "GET",
                        url: "{{ route('sites.compatible-options') }}",
                        data: {
                            categories: selectedCategories,
                            option_type: 'purposes'
                        },
                        dataType: "json",
                        success: function(response) {
                            // Reset all purposes
                            $('.filter-purpose').prop('checked', false).closest('.form-check').addClass('unsupported-option');
                            
                            // Enable compatible purposes
                            if (response.compatible_options) {
                                response.compatible_options.forEach(function(purposeId) {
                                    $(`#filter_purpose${purposeId}`).closest('.form-check').removeClass('unsupported-option');
                                });
                            }
                            
                            // Add note about compatibility
                            if (!$('#purposeCompatibilityNote').length) {
                                $('#step3 .border.p-3').after(
                                    '<span id="purposeCompatibilityNote" class="compatibility-note">' +
                                    'Crossed-out options are not compatible with selected categories</span>'
                                );
                            }
                        },
                        error: function() {
                            // If error, show all purposes
                            $('.filter-purpose').closest('.form-check').removeClass('unsupported-option');
                        }
                    });
                }
                
                // Filter features based on selected categories
                function filterFeaturesByCategories() {
                    const selectedCategories = [];
                    $('.filter-category:checked').each(function() {
                        selectedCategories.push($(this).val());
                    });
                    
                    if (selectedCategories.length === 0) {
                        // If no categories selected, show all features
                        $('.feature-checkbox').closest('.form-check').removeClass('unsupported-option');
                        return;
                    }
                    
                    // Get compatible features via AJAX
                    $.ajax({
                        type: "GET",
                        url: "{{ route('sites.compatible-options') }}",
                        data: {
                            categories: selectedCategories,
                            option_type: 'features'
                        },
                        dataType: "json",
                        success: function(response) {
                            // Reset all features
                            $('.feature-checkbox').prop('checked', false).closest('.form-check').addClass('unsupported-option');
                            
                            // Enable compatible features
                            if (response.compatible_options) {
                                response.compatible_options.forEach(function(featureId) {
                                    $(`#feature${featureId}`).closest('.form-check').removeClass('unsupported-option');
                                });
                            }
                            
                            // Add note about compatibility
                            if (!$('#featureCompatibilityNote').length) {
                                $('#step4 .border.p-3').after(
                                    '<span id="featureCompatibilityNote" class="compatibility-note">' +
                                    'Crossed-out options are not compatible with selected categories</span>'
                                );
                            }
                        },
                        error: function() {
                            // If error, show all features
                            $('.feature-checkbox').closest('.form-check').removeClass('unsupported-option');
                        }
                    });
                }
                
                // When category selection changes, update next button state
                $('.filter-category').on('change', function() {
                    const anySelected = $('.filter-category:checked').length > 0;
                    const $nextButton = $('#step1 .next-step');
                    
                    if (anySelected) {
                        $nextButton.removeClass('btn-secondary').addClass('btn-primary');
                        $nextButton.html('Next <i class="fas fa-arrow-right ms-1"></i>');
                    } else {
                        $nextButton.removeClass('btn-primary').addClass('btn-secondary');
                        $nextButton.text('Next');
                    }
                });
            });
        </script>
    @endpush

</x-app-layout>

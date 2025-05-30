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

            .next-step,
            .prev-step {
                transition: all 0.2s;
            }

            .next-step:hover,
            .prev-step:hover {
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

            /* Enhanced unsupported option styling */
            .unsupported-option {
                opacity: 0.5 !important;
                text-decoration: line-through !important;
            }

            /* Only hide completely when explicitly told to do so - highest specificity */
            .compatibility-container.hide-incompatible .form-check.unsupported-option,
            .hide-incompatible .form-check.unsupported-option,
            #addSiteForm .compatibility-container.hide-incompatible .form-check.unsupported-option,
            #editSiteForm .compatibility-container.hide-incompatible .form-check.unsupported-option {
                display: none !important;
                visibility: hidden !important;
            }

            /* Extra specific rules to ensure the right elements are hidden */
            #addSiteForm .compatibility-container.hide-incompatible .unsupported-option,
            #editSiteForm .compatibility-container.hide-incompatible .unsupported-option,
            #filter_countriesContainer.hide-incompatible .unsupported-option,
            #step3 .border.hide-incompatible .unsupported-option,
            #step4 .border.hide-incompatible .unsupported-option {
                display: none !important;
                visibility: hidden !important;
            }

            .compatibility-note {
                font-size: 0.8rem;
                color: #0d6efd;
                margin-top: 5px;
                display: block;
                background-color: rgba(13, 110, 253, 0.05);
                padding: 5px;
                border-left: 3px solid #0d6efd;
                border-radius: 0 4px 4px 0;
            }

            /* Category compatibility section highlight */
            .compatibility-active {
                border: 1px solid #0d6efd !important;
                background-color: rgba(13, 110, 253, 0.05);
                box-shadow: 0 0 5px rgba(13, 110, 253, 0.2);
            }

            /* Loading indicator for compatibility filtering */
            .loading-spinner {
                padding: 5px;
                border-radius: 4px;
                background-color: rgba(255, 255, 255, 0.8);
            }

            /* Domain validation styles */
            .is-loading {
                background-image: url('data:image/svg+xml;charset=UTF-8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" preserveAspectRatio="xMidYMid"><circle cx="50" cy="50" fill="none" stroke="%236c757d" stroke-width="10" r="35" stroke-dasharray="164.93361431346415 56.97787143782138"><animateTransform attributeName="transform" type="rotate" repeatCount="indefinite" dur="1s" values="0 50 50;360 50 50" keyTimes="0;1"></animateTransform></circle></svg>');
                background-position: calc(100% - 10px) center;
                background-repeat: no-repeat;
                background-size: 20px;
            }

            /* Override Bootstrap validation icons */
            .form-control.is-invalid,
            .form-control.is-valid {
                padding-right: 2.375rem;
                background-position: right calc(0.375em + 0.1875rem) center;
            }

            /* Enhanced validation styles */
            .form-control.is-valid {
                border-color: #28a745;
                border-width: 2px;
                box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25);
            }

            .form-control.is-invalid {
                border-color: #dc3545;
                border-width: 2px;
                box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
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
                                        <strong>Category-Based Filtering:</strong> Your category selection will determine
                                        which countries, work purposes, and features are available in the following steps.
                                        Options that are not compatible with your selected categories will be hidden.
                                    </div>

                                    <div class="mb-3 border p-3 rounded" style="max-height: 200px; overflow-y: auto;">
                                        <div class="row">
                                            @foreach ($categories as $category)
                                                <div class="col-md-4 mb-2">
                                                    <div class="form-check">
                                                        <input class="form-check-input filter-category" type="checkbox"
                                                            value="{{ $category->id }}"
                                                            id="filter_category{{ $category->id }}"
                                                            data-category-name="{{ $category->name }}">
                                                        <label class="form-check-label"
                                                            for="filter_category{{ $category->id }}">
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
                                    <div id="filter_countriesContainer" class="mb-3 border p-3 rounded"
                                        style="max-height: 200px; overflow-y: auto;">
                                        <div class="row">
                                            @foreach ($countries as $country)
                                                <div class="col-md-4 mb-2">
                                                    <div class="form-check">
                                                        <input class="form-check-input filter-country" type="checkbox"
                                                            value="{{ $country->id }}"
                                                            id="filter_country{{ $country->id }}"
                                                            data-country-name="{{ $country->name }}">
                                                        <label class="form-check-label"
                                                            for="filter_country{{ $country->id }}">
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
                                            @foreach ($purposes as $purpose)
                                                <div class="col-md-4 mb-2">
                                                    <div class="form-check">
                                                        <input class="form-check-input filter-purpose" type="checkbox"
                                                            value="{{ $purpose->id }}"
                                                            id="filter_purpose{{ $purpose->id }}"
                                                            data-purpose-name="{{ $purpose->name }}">
                                                        <label class="form-check-label"
                                                            for="filter_purpose{{ $purpose->id }}">
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
                                        <label for="filter_rating" class="form-label">Minimum Rating: <span
                                                id="ratingValue">0</span></label>
                                        <input type="range" class="form-range" min="0" max="10"
                                            step="0.5" id="filter_rating">
                                    </div>

                                    <div class="mb-3">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="sortByRating" checked>
                                            <label class="form-check-label" for="sortByRating">Sort by Rating (High to
                                                Low)</label>
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
                                    @if (auth()->user()->canManageSites())
                                        <a href="#" data-bs-toggle="modal" data-bs-target="#AddSiteModal"
                                            class="btn btn-primary">
                                            <i class="fas fa-plus me-2"></i> Add New Site
                                        </a>
                                    @endif
                                </div>
                            </div>

                            <!-- No data message -->
                            <div id="noFiltersMessage"
                                class="text-center py-5 @if (isset($sites) && $sites->count() > 0) d-none @endif">
                                <i class="fas fa-filter fa-3x text-muted mb-3"></i>
                                <h4 class="text-muted">Please use the filters above to view specific sites</h4>
                                <p class="text-muted">Select options in each step to display matching sites</p>
                            </div>

                            <div id="sitesTableContainer" class="@if (!isset($sites) || $sites->count() == 0) d-none @endif">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th scope="col">Domain</th>
                                                <th scope="col">Complete URL</th>
                                                <th scope="col">DA</th>
                                                <th scope="col">Video</th>
                                                <th scope="col">Status</th>
                                                <th scope="col">Server Status</th>
                                                <th scope="col">Rating</th>
                                                <th scope="col">Categories</th>
                                                <th scope="col">Countries</th>
                                                <th scope="col">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr id="loading-spinner" style="display: none;">
                                                <td colspan="9" class="text-center">
                                                    <div class="d-flex justify-content-center align-items-center py-4">
                                                        <div class="spinner-border text-primary" role="status">
                                                            <span class="visually-hidden">Loading...</span>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr id="no-results" class="d-none">
                                                <td colspan="9" class="text-center py-4">
                                                    <i class="fas fa-search fa-2x text-muted mb-3"></i>
                                                    <h5 class="text-muted">No matching sites found</h5>
                                                    <p class="text-muted">Try adjusting your filter criteria</p>
                                                </td>
                                            </tr>

                                            @if (isset($sites) && $sites->count() > 0)
                                                @foreach ($sites as $site)
                                                    <tr class="default-site-row">
                                                        <td>{{ $site['url'] }}</td>
                                                        <td>{{ $site['complete_url'] ?? 'N/A' }}</td>
                                                        <td>{{ $site['da'] ?? 'N/A' }}</td>
                                                        <td>
                                                            @if (!empty($site['video_link']))
                                                                <button type="button"
                                                                    class="btn btn-sm btn-outline-primary play-video-btn"
                                                                    data-video-url="{{ $site['video_link'] }}">
                                                                    <i class="fas fa-play-circle"></i> Play
                                                                </button>
                                                            @else
                                                                <span class="text-muted">No video</span>
                                                            @endif
                                                        </td>
                                                        <td>{{ $site['status'] }}</td>
                                                        <td>{{ $site['server_status'] ?? 'Unknown' }}</td>
                                                        <td
                                                            class="{{ $site['rating'] >= 7 ? 'text-success fw-bold' : ($site['rating'] >= 4 ? 'text-warning' : 'text-danger') }}">
                                                            {{ number_format($site['rating'], 1) }} /
                                                            {{ $site['max_rating'] ?? 10 }}
                                                        </td>
                                                        <td>{{ $site['categories_list'] }}</td>
                                                        <td>{{ $site['countries_list'] }}</td>
                                                        <td>
                                                            <div class="btn-group" role="group"
                                                                aria-label="Basic example">
                                                                @if (auth()->user()->canManageSites())
                                                                    <button type="button" value="{{ $site['id'] }}"
                                                                        class="btn btn-primary edit-btn"><i
                                                                            class='bx bxs-edit'></i></button>
                                                                    <button type="button" value="{{ $site['id'] }}"
                                                                        class="btn btn-danger delete-btn"><i
                                                                            class='bx bxs-trash'></i></button>
                                                                @else
                                                                    <button type="button" class="btn btn-secondary"
                                                                        disabled><i class='bx bxs-lock'></i></button>
                                                                @endif
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            @endif
                                        </tbody>
                                    </table>
                                </div>

                                @if (isset($sites) && $sites->count() > 0)
                                    <div class="pagination-container d-flex justify-content-center mt-4">
                                        {{ $sites->links() }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add Modal -->
        <div class="modal fade" id="AddSiteModal" tabindex="-1" aria-labelledby="exampleModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">

                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="exampleModalLabel">Add Site</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                        <form id="addSiteForm" action="{{ route('sites.store') }}" method="post">
                            @csrf
                            <div class="row mb-3">
                                <div class="col-md-8">
                                    <div class="form-group mb-3">
                                        <label for="url">Domain Name</label>
                                        <div class="input-group">
                                            <input type="text" class="form-control" id="url" name="url"
                                                required placeholder="example.com">
                                            <button class="btn btn-secondary" type="button" id="checkDomainBtn">Check
                                                Status</button>
                                        </div>
                                        <small class="text-muted">Enter domain name with optional subdomain (e.g.,
                                            example.com or blog.example.com)</small>
                                        <div class="invalid-feedback" id="url_error"></div>
                                        <div id="serverStatusFeedback" class="mt-2 d-none">
                                            <div class="alert alert-info" role="alert">
                                                <span id="serverStatusMessage">Checking domain...</span>
                                            </div>
                                        </div>
                                        <div class="form-check mt-2 d-none" id="ignoreServerStatusContainer">
                                            <input class="form-check-input" type="checkbox" id="ignoreServerStatus">
                                            <label class="form-check-label" for="ignoreServerStatus">
                                                Ignore server status and allow submission anyway
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label for="da">DA (Domain Authority)</label>
                                        <input type="number" class="form-control" id="da" name="da"
                                            min="0" max="100" placeholder="1-100">
                                        <small class="text-muted">Enter a value between 0-100</small>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group mb-3">
                                <label for="description">Description</label>
                                <textarea class="form-control" id="description" name="description"></textarea>
                            </div>

                            <div class="form-group mb-3">
                                <label for="video_link">YouTube Video Link (Optional)</label>
                                <input type="url" class="form-control" id="video_link" name="video_link"
                                    placeholder="https://www.youtube.com/watch?v=...">
                                <small class="text-muted">Enter a YouTube video URL that will be embedded in a
                                    modal</small>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="status">Status</label>
                                        <select class="form-control" id="status" name="status">
                                            <option value="Live" selected>Live</option>
                                            <option value="Pending">Pending</option>
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
                                        <input type="text" class="form-control" id="theme" name="theme"
                                            value="default">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label>Categories</label>
                                        <div class="form-check form-switch mb-2">
                                            <input class="form-check-input" type="checkbox" id="hideIncompatibleOptions"
                                                checked>
                                            <label class="form-check-label" for="hideIncompatibleOptions">
                                                <small>Hide incompatible options</small>
                                            </label>
                                        </div>
                                        <div class="border p-3 rounded compatibility-container"
                                            style="max-height: 150px; overflow-y: auto;">
                                            @foreach ($categories as $category)
                                                <div class="form-check">
                                                    <input class="form-check-input add-category" type="checkbox"
                                                        name="categories[]" value="{{ $category->id }}"
                                                        id="category{{ $category->id }}"
                                                        data-category-name="{{ $category->name }}">
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
                                        <div class="border p-3 rounded compatibility-container"
                                            style="max-height: 150px; overflow-y: auto;">
                                            @foreach ($purposes as $purpose)
                                                <div class="form-check">
                                                    <input class="form-check-input add-purpose" type="checkbox"
                                                        name="purposes[]" value="{{ $purpose->id }}"
                                                        id="purpose{{ $purpose->id }}"
                                                        data-purpose-name="{{ $purpose->name }}">
                                                    <label class="form-check-label" for="purpose{{ $purpose->id }}">
                                                        {{ $purpose->name }}
                                                    </label>
                                                </div>
                                            @endforeach
                                        </div>
                                        <small class="text-muted">Select multiple work purposes</small>
                                        <span id="purposeCompatibilityNoteAdd" class="compatibility-note d-none">
                                            <i class="fas fa-info-circle me-1"></i> Only showing options compatible with
                                            selected categories
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group mb-3">
                                <label>Countries</label>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" name="is_global" value="1"
                                        id="isGlobal">
                                    <label class="form-check-label" for="isGlobal">
                                        <strong>Global (All Countries)</strong>
                                    </label>
                                </div>
                                <div id="countriesContainer" class="border p-3 rounded compatibility-container"
                                    style="max-height: 150px; overflow-y: auto;">
                                    @foreach ($countries as $country)
                                        <div class="form-check">
                                            <input class="form-check-input country-checkbox add-country" type="checkbox"
                                                name="countries[]" value="{{ $country->id }}"
                                                id="country{{ $country->id }}"
                                                data-country-name="{{ $country->name }}">
                                            <label class="form-check-label" for="country{{ $country->id }}">
                                                {{ $country->name }}
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                                <small class="text-muted">Select multiple countries or check Global</small>
                                <span id="countryCompatibilityNoteAdd" class="compatibility-note d-none">
                                    <i class="fas fa-info-circle me-1"></i> Only showing options compatible with selected
                                    categories
                                </span>
                            </div>

                            <div class="form-group mb-3">
                                <label>Site Features (Rating System)</label>
                                <div class="border p-3 rounded compatibility-container"
                                    style="max-height: 200px; overflow-y: auto;">
                                    <div class="row">
                                        @foreach ($features as $feature)
                                            <div class="col-md-6 mb-2">
                                                <div class="form-check">
                                                    <input class="form-check-input feature-checkbox add-feature"
                                                        type="checkbox" name="features[]" value="{{ $feature->id }}"
                                                        id="feature{{ $feature->id }}"
                                                        data-points="{{ $feature->points }}">
                                                    <label class="form-check-label" for="feature{{ $feature->id }}">
                                                        {{ $feature->name }} <span
                                                            class="badge bg-info">{{ $feature->points }} pts</span>
                                                    </label>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                                <div class="progress mt-2">
                                    <div id="ratingProgress" class="progress-bar" role="progressbar" style="width: 0%;"
                                        aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0%</div>
                                </div>
                                <small class="text-muted">Total rating: <span id="currentRating">0</span> out of <span
                                        id="maxRating">{{ $features->sum('points') }}</span> points</small>
                                <span id="featureCompatibilityNoteAdd" class="compatibility-note d-none">
                                    <i class="fas fa-info-circle me-1"></i> Only showing options compatible with selected
                                    categories
                                </span>
                            </div>

                            <div class="form-group mb-3">
                                <label for="complete_url">Complete URL</label>
                                <input type="text" class="form-control" id="complete_url" name="complete_url"
                                    placeholder="https://example.com">
                                <small class="text-muted">Enter the full URL including http:// or https://</small>
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
                            @method('PUT')
                            <input type="hidden" id="edit_id" name="edit_id">

                            <div class="row mb-3">
                                <div class="col-md-8">
                                    <div class="form-group mb-3">
                                        <label for="edit_url">Domain Name</label>
                                        <div class="input-group">
                                            <input type="text" class="form-control" id="edit_url" name="edit_url"
                                                required placeholder="example.com">
                                            <button class="btn btn-secondary" type="button"
                                                id="editCheckDomainBtn">Check Status</button>
                                        </div>
                                        <small class="text-muted">Enter domain name with optional subdomain (e.g.,
                                            example.com or blog.example.com)</small>
                                        <div class="invalid-feedback" id="edit_url_error"></div>
                                        <div id="editServerStatusFeedback" class="mt-2 d-none">
                                            <div class="alert alert-info" role="alert">
                                                <span id="editServerStatusMessage">Checking domain...</span>
                                            </div>
                                        </div>
                                        <div class="form-check mt-2 d-none" id="editIgnoreServerStatusContainer">
                                            <input class="form-check-input" type="checkbox" id="editIgnoreServerStatus">
                                            <label class="form-check-label" for="editIgnoreServerStatus">
                                                Ignore server status and allow submission anyway
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label for="edit_da">DA (Domain Authority)</label>
                                        <input type="number" class="form-control" id="edit_da" name="edit_da"
                                            min="0" max="100" placeholder="1-100">
                                        <small class="text-muted">Enter a value between 0-100</small>
                                    </div>
                                </div>
                                <div class="form-group mb-3">
                                    <label for="edit_complete_url">Complete URL</label>
                                    <input type="text" class="form-control" id="edit_complete_url"
                                        name="edit_complete_url" placeholder="https://example.com">
                                    <small class="text-muted">Enter the full URL including http:// or https://</small>
                                </div>
                            </div>

                            <div class="form-group mb-3">
                                <label for="edit_description">Description</label>
                                <textarea class="form-control" id="edit_description" name="edit_description"></textarea>
                            </div>

                            <div class="form-group mb-3">
                                <label for="edit_video_link">YouTube Video Link (Optional)</label>
                                <input type="url" class="form-control" id="edit_video_link" name="edit_video_link"
                                    placeholder="https://www.youtube.com/watch?v=...">
                                <small class="text-muted">Enter a YouTube video URL that will be embedded in a
                                    modal</small>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="edit_status">Status</label>
                                        <select class="form-control" id="edit_status" name="edit_status">
                                            <option value="Live">Live</option>
                                            <option value="Pending">Pending</option>
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
                                        <input type="text" class="form-control" id="edit_theme" name="edit_theme"
                                            value="default">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label>Categories</label>
                                        <div class="form-check form-switch mb-2">
                                            <input class="form-check-input" type="checkbox"
                                                id="hideIncompatibleOptionsEdit" checked>
                                            <label class="form-check-label" for="hideIncompatibleOptionsEdit">
                                                <small>Hide incompatible options</small>
                                            </label>
                                        </div>
                                        <div class="border p-3 rounded compatibility-container"
                                            style="max-height: 150px; overflow-y: auto;">
                                            @foreach ($categories as $category)
                                                <div class="form-check">
                                                    <input class="form-check-input edit-category" type="checkbox"
                                                        name="categories[]" value="{{ $category->id }}"
                                                        id="edit_category{{ $category->id }}"
                                                        data-category-name="{{ $category->name }}">
                                                    <label class="form-check-label"
                                                        for="edit_category{{ $category->id }}">
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
                                        <div class="border p-3 rounded compatibility-container"
                                            style="max-height: 150px; overflow-y: auto;">
                                            @foreach ($purposes as $purpose)
                                                <div class="form-check">
                                                    <input class="form-check-input edit-purpose" type="checkbox"
                                                        name="purposes[]" value="{{ $purpose->id }}"
                                                        id="edit_purpose{{ $purpose->id }}"
                                                        data-purpose-name="{{ $purpose->name }}">
                                                    <label class="form-check-label"
                                                        for="edit_purpose{{ $purpose->id }}">
                                                        {{ $purpose->name }}
                                                    </label>
                                                </div>
                                            @endforeach
                                        </div>
                                        <small class="text-muted">Select multiple work purposes</small>
                                        <span id="purposeCompatibilityNoteEdit" class="compatibility-note d-none">
                                            <i class="fas fa-info-circle me-1"></i> Only showing options compatible with
                                            selected categories
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group mb-3">
                                <label>Countries</label>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" name="is_global" value="1"
                                        id="edit_isGlobal">
                                    <label class="form-check-label" for="edit_isGlobal">
                                        <strong>Global (All Countries)</strong>
                                    </label>
                                </div>
                                <div id="edit_countriesContainer" class="border p-3 rounded compatibility-container"
                                    style="max-height: 150px; overflow-y: auto;">
                                    @foreach ($countries as $country)
                                        <div class="form-check">
                                            <input class="form-check-input edit-country-checkbox edit-country"
                                                type="checkbox" name="countries[]" value="{{ $country->id }}"
                                                id="edit_country{{ $country->id }}"
                                                data-country-name="{{ $country->name }}">
                                            <label class="form-check-label" for="edit_country{{ $country->id }}">
                                                {{ $country->name }}
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                                <small class="text-muted">Select multiple countries or check Global</small>
                                <span id="countryCompatibilityNoteEdit" class="compatibility-note d-none">
                                    <i class="fas fa-info-circle me-1"></i> Only showing options compatible with selected
                                    categories
                                </span>
                            </div>

                            <div class="form-group mb-3">
                                <label>Site Features (Rating System)</label>
                                <div class="border p-3 rounded compatibility-container"
                                    style="max-height: 200px; overflow-y: auto;">
                                    <div class="row">
                                        @foreach ($features as $feature)
                                            <div class="col-md-6 mb-2">
                                                <div class="form-check">
                                                    <input class="form-check-input edit-feature-checkbox edit-feature"
                                                        type="checkbox" name="features[]" value="{{ $feature->id }}"
                                                        id="edit_feature{{ $feature->id }}"
                                                        data-points="{{ $feature->points }}">
                                                    <label class="form-check-label"
                                                        for="edit_feature{{ $feature->id }}">
                                                        {{ $feature->name }} <span
                                                            class="badge bg-info">{{ $feature->points }} pts</span>
                                                    </label>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                                <div class="progress mt-2">
                                    <div id="edit_ratingProgress" class="progress-bar" role="progressbar"
                                        style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0%
                                    </div>
                                </div>
                                <small class="text-muted">Total rating: <span id="edit_currentRating">0</span> out of
                                    <span id="edit_maxRating">{{ $features->sum('points') }}</span> points</small>
                                <span id="featureCompatibilityNoteEdit" class="compatibility-note d-none">
                                    <i class="fas fa-info-circle me-1"></i> Only showing options compatible with selected
                                    categories
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
            // Global route URLs for JavaScript access
            window.routeUrls = {
                checkReachability: "{{ route('sites.check-reachability') }}",
                compatibleOptions: "{{ route('sites.compatible-options') }}"
            };
        </script>
        <script src="{{ asset('js/site-compatibility.js') }}"></script>
        <script src="{{ asset('js/site-ratings.js') }}"></script>
        <script src="{{ asset('js/domain-checker.js') }}"></script>
        <script src="{{ asset('js/add-site-category-filter.js') }}"></script>
        <script src="{{ asset('js/edit-site-category-filter.js') }}"></script>
        <script src="{{ asset('js/site-form-handlers.js') }}"></script>
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

                // Connect category selection to filtering
                $('.filter-category').on('change', function() {
                    updateSelectedCategoriesBadges();
                    // Pre-load the countries data for the next step
                    filterCountriesByCategories();
                });

                // Apply filters button
                $('#applyFilters').on('click', function() {
                    const categories = [];
                    $('.filter-category:checked').each(function() {
                        categories.push($(this).val());
                    });

                    const isGlobal = $('#filter_global').is(':checked');

                    const countries = [];
                    $('.filter-country:checked').each(function() {
                        countries.push($(this).val());
                    });

                    const purposes = [];
                    $('.filter-purpose:checked').each(function() {
                        purposes.push($(this).val());
                    });

                    const minRating = parseFloat($('#filter_rating').val());
                    const sortByRating = $('#sortByRating').is(':checked');

                    fetchFilteredSites(categories, isGlobal, countries, purposes, minRating, sortByRating);

                    // Update filter badges
                    updateFilterBadges(categories, isGlobal, countries, purposes, minRating);
                });

                // Reset filters
                $('#resetFilters').on('click', function() {
                    // Reset all checkboxes
                    $('.filter-category, .filter-country, .filter-purpose, #filter_global').prop('checked',
                        false);
                    $('.filter-country, .filter-purpose').closest('.form-check').removeClass(
                        'unsupported-option');
                    $('#filter_countriesContainer').removeClass('opacity-50 border-primary');

                    // Reset rating slider
                    $('#filter_rating').val(0);
                    $('#ratingValue').text('0');

                    // Set sort option to default
                    $('#sortByRating').prop('checked', true);

                    // Show sites table with default pagination if available
                    if ($('tr.default-site-row').length > 0) {
                        $('#sitesTableContainer').removeClass('d-none');
                        $('#noFiltersMessage').addClass('d-none');
                        $('tr.default-site-row').removeClass('d-none');
                        $('.pagination-container').removeClass('d-none');
                    } else {
                        // Hide sites table and show no filters message
                        $('#sitesTableContainer').addClass('d-none');
                        $('#noFiltersMessage').removeClass('d-none');
                    }

                    // Hide filtered results elements
                    $('#no-results').addClass('d-none');
                    $('tbody tr:not(.default-site-row):not(#loading-spinner):not(#no-results)').remove();

                    // Hide Excel button
                    $('#copyToExcel').addClass('d-none');

                    // Clear badges and compatibility notes
                    $('#selectedCategoriesBadges, #selectedCategoriesBadges2, #selectedCategoriesBadges3')
                        .empty();
                    $('.compatibility-note').remove();

                    // Remove hide-incompatible classes
                    $('#filter_countriesContainer, #step3 .border, #step4 .border').removeClass(
                        'hide-incompatible');

                    // Reset to first step
                    $('.filter-step').removeClass('active').addClass('d-none');
                    $('#step1').removeClass('d-none').addClass('active');
                });

                // Initial setup - make sure incompatible options are properly styled
                updateSelectedCategoriesBadges();

                // Handle hide incompatible option for countries in filter panel
                $('#hideIncompatibleCountries').on('change', function() {
                    if ($(this).is(':checked')) {
                        $('#filter_countriesContainer').addClass('hide-incompatible');
                    } else {
                        $('#filter_countriesContainer').removeClass('hide-incompatible');
                    }
                });

                // Handle hide incompatible option for purposes in filter panel
                $('#hideIncompatiblePurposes').on('change', function() {
                    if ($(this).is(':checked')) {
                        $('#step3').find('.border').addClass('hide-incompatible');
                    } else {
                        $('#step3').find('.border').removeClass('hide-incompatible');
                    }
                });
            });

            // Function to filter purposes based on selected categories
            function filterPurposesByCategories() {
                const selectedCategories = [];
                $('.filter-category:checked').each(function() {
                    selectedCategories.push(parseInt($(this).val()));
                });

                if (selectedCategories.length === 0) {
                    // Reset if no categories selected
                    $('.filter-purpose').closest('.form-check').removeClass('unsupported-option');
                    return;
                }

                // Add loading indicator
                const loadingHtml =
                    '<div class="loading-indicator py-2 text-center"><div class="spinner-border spinner-border-sm text-primary me-2"></div><span>Loading compatible purposes...</span></div>';
                $('#selectedCategoriesBadges2').append(loadingHtml);

                // Fetch compatible purposes from server
                $.ajax({
                    type: "GET",
                    url: routeUrls.compatibleOptions,
                    data: {
                        categories: selectedCategories,
                        option_types: ['purposes']
                    },
                    dataType: "json",
                    success: function(response) {
                        // Remove loading indicator
                        $('.loading-indicator').remove();

                        if (!response.success) {
                            // Show error
                            const errorMsg = $(
                                '<div class="alert alert-danger py-1 px-2 mt-1" style="font-size: 0.8rem;"><i class="fas fa-exclamation-triangle"></i> Error loading compatible purposes</div>'
                                );
                            $('#selectedCategoriesBadges2').append(errorMsg);

                            // Remove after 3 seconds
                            setTimeout(() => {
                                errorMsg.fadeOut(300, function() {
                                    $(this).remove();
                                });
                            }, 3000);
                            return;
                        }

                        const compatiblePurposes = response.purposes || [];

                        // First reset all options to remove any previous unsupported-option classes
                        $('.filter-purpose').closest('.form-check').removeClass('unsupported-option');

                        // Track processed IDs to avoid duplicates
                        const processedIds = new Set();

                        // Mark compatible/incompatible purposes - IMPORTANT fix to target the form-check parent
                        $('.filter-purpose').each(function() {
                            const purposeId = parseInt($(this).val());

                            // Skip if already processed
                            if (processedIds.has(purposeId)) {
                                return;
                            }
                            processedIds.add(purposeId);

                            if (!compatiblePurposes.includes(purposeId)) {
                                // Uncheck and mark as incompatible
                                $(this).prop('checked', false);
                                // Apply unsupported-option class to the form-check parent
                                $(this).closest('.form-check').addClass('unsupported-option');
                            }
                        });

                        // Add note about filtered purposes
                        const compatibilityNote = $(`
                            <div class="compatibility-note mt-2">
                                <i class="fas fa-filter me-1"></i>
                                ${compatiblePurposes.length} compatible purposes shown
                                <div class="form-check form-switch mt-1">
                                    <input class="form-check-input" type="checkbox" id="hideIncompatiblePurposes">
                                    <label class="form-check-label" for="hideIncompatiblePurposes">
                                        <small>Hide incompatible purposes</small>
                                    </label>
                                </div>
                            </div>
                        `);

                        $('#selectedCategoriesBadges2').append(compatibilityNote);

                        // Handle hide incompatible option - make sure to use document to handle dynamically added elements
                        $(document).on('change', '#hideIncompatiblePurposes', function() {
                            if ($(this).is(':checked')) {
                                $('#step3').find('.border').addClass('hide-incompatible');
                            } else {
                                $('#step3').find('.border').removeClass('hide-incompatible');
                            }
                        });
                    },
                    error: function(xhr) {
                        // Remove loading indicator
                        $('.loading-indicator').remove();

                        // Show error
                        const errorMsg = $(
                            '<div class="alert alert-danger py-1 px-2 mt-1" style="font-size: 0.8rem;"><i class="fas fa-exclamation-triangle"></i> Error loading compatibility data</div>'
                            );
                        $('#selectedCategoriesBadges2').append(errorMsg);

                        // Log error
                        console.error('Error fetching compatible purposes:', xhr.responseText);

                        // Remove after 3 seconds
                        setTimeout(() => {
                            errorMsg.fadeOut(300, function() {
                                $(this).remove();
                            });
                        }, 3000);
                    }
                });
            }

            // Function to filter countries based on selected categories
            function filterCountriesByCategories() {
                const selectedCategories = [];
                $('.filter-category:checked').each(function() {
                    selectedCategories.push(parseInt($(this).val()));
                });

                if (selectedCategories.length === 0) {
                    // Reset if no categories selected
                    $('.filter-country').closest('.form-check').removeClass('unsupported-option');
                    return;
                }

                // Add loading indicator
                const loadingHtml =
                    '<div class="loading-indicator py-2 text-center"><div class="spinner-border spinner-border-sm text-primary me-2"></div><span>Loading compatible countries...</span></div>';
                $('#selectedCategoriesBadges').append(loadingHtml);

                // Fetch compatible countries from server
                $.ajax({
                    type: "GET",
                    url: routeUrls.compatibleOptions,
                    data: {
                        categories: selectedCategories,
                        option_types: ['countries']
                    },
                    dataType: "json",
                    success: function(response) {
                        // Remove loading indicator
                        $('.loading-indicator').remove();

                        if (!response.success) {
                            // Show error
                            const errorMsg = $(
                                '<div class="alert alert-danger py-1 px-2 mt-1" style="font-size: 0.8rem;"><i class="fas fa-exclamation-triangle"></i> Error loading compatible countries</div>'
                                );
                            $('#selectedCategoriesBadges').append(errorMsg);

                            // Remove after 3 seconds
                            setTimeout(() => {
                                errorMsg.fadeOut(300, function() {
                                    $(this).remove();
                                });
                            }, 3000);
                            return;
                        }

                        const compatibleCountries = response.countries || [];

                        // First reset all options
                        $('.filter-country').closest('.form-check').removeClass('unsupported-option');

                        // Track processed IDs to avoid duplicates
                        const processedIds = new Set();

                        // Mark compatible/incompatible countries
                        $('.filter-country').each(function() {
                            const countryId = parseInt($(this).val());

                            // Skip if already processed
                            if (processedIds.has(countryId)) {
                                return;
                            }
                            processedIds.add(countryId);

                            if (!compatibleCountries.includes(countryId)) {
                                // Uncheck incompatible options
                                $(this).prop('checked', false);
                                // Apply unsupported-option class to the form-check parent
                                $(this).closest('.form-check').addClass('unsupported-option');
                            }
                        });

                        // Add note about filtered countries
                        const compatibilityNote = $(`
                            <div class="compatibility-note mt-2">
                                <i class="fas fa-filter me-1"></i>
                                ${compatibleCountries.length} compatible countries shown
                                <div class="form-check form-switch mt-1">
                                    <input class="form-check-input" type="checkbox" id="hideIncompatibleCountries">
                                    <label class="form-check-label" for="hideIncompatibleCountries">
                                        <small>Hide incompatible countries</small>
                                    </label>
                                </div>
                            </div>
                        `);

                        $('#selectedCategoriesBadges').append(compatibilityNote);

                        // Handle hide incompatible option
                        $(document).on('change', '#hideIncompatibleCountries', function() {
                            if ($(this).is(':checked')) {
                                $('#filter_countriesContainer').addClass('hide-incompatible');
                            } else {
                                $('#filter_countriesContainer').removeClass('hide-incompatible');
                            }
                        });
                    },
                    error: function(xhr) {
                        // Remove loading indicator
                        $('.loading-indicator').remove();

                        // Show error
                        const errorMsg = $(
                            '<div class="alert alert-danger py-1 px-2 mt-1" style="font-size: 0.8rem;"><i class="fas fa-exclamation-triangle"></i> Error loading compatibility data</div>'
                            );
                        $('#selectedCategoriesBadges').append(errorMsg);

                        // Log error
                        console.error('Error fetching compatible countries:', xhr.responseText);

                        // Remove after 3 seconds
                        setTimeout(() => {
                            errorMsg.fadeOut(300, function() {
                                $(this).remove();
                            });
                        }, 3000);
                    }
                });
            }
        </script>
    @endpush

</x-app-layout>

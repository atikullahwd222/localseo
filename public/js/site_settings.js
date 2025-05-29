$(document).ready(function() {
    // Setup CSRF token for all AJAX requests
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // Initialize Select2
    initializeSelect2();
    
    // Add category filter controls
    addFilteringControls();

    // Load all data on page load
    loadCategories();
    loadCountries();
    loadPurposes();
    loadFeatures();

    // ============== CATEGORY FUNCTIONS ==============
    
    // Load all categories
    function loadCategories() {
        $.ajax({
            url: '/site-settings/categories',
            type: 'GET',
            success: function(response) {
                if (response.status === 200) {
                    $('#categoryTable tbody').empty();
                    
                    if (response.categories.length === 0) {
                        $('#categoryTable tbody').html('<tr><td colspan="4" class="text-center">No categories found</td></tr>');
                        return;
                    }
                    
                    $.each(response.categories, function(index, category) {
                        $('#categoryTable tbody').append(`
                            <tr>
                                <td>${index + 1}</td>
                                <td>${category.name}</td>
                                <td>${category.description || '-'}</td>
                                <td>
                                    <button class="btn btn-sm btn-info edit-category" data-id="${category.id}">
                                        <i class="bx bx-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger delete-category" data-id="${category.id}">
                                        <i class="bx bx-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        `);
                    });
                    
                    // Populate category dropdowns after loading
                    populateCategoryDropdowns(response.categories);
                }
            },
            error: function(xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed to load categories'
                });
            }
        });
    }
    
    // Add category
    $('#saveCategoryBtn').on('click', function() {
        $(this).find('.btn-text').hide();
        $(this).find('.spinner-border').removeClass('d-none');
        $('.is-invalid').removeClass('is-invalid');
        
        const categoryData = {
            name: $('#category_name').val(),
            description: $('#category_description').val()
        };
        
        $.ajax({
            url: '/site-settings/categories',
            type: 'POST',
            data: categoryData,
            success: function(response) {
                $('#saveCategoryBtn').find('.btn-text').show();
                $('#saveCategoryBtn').find('.spinner-border').addClass('d-none');
                
                if (response.status === 400) {
                    handleValidationErrors(response.errors, 'category_');
                    return;
                }
                
                if (response.status === 200) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: response.message
                    });
                    
                    $('#addCategoryModal').modal('hide');
                    $('#addCategoryForm')[0].reset();
                    loadCategories();
                }
            },
            error: function(xhr) {
                $('#saveCategoryBtn').find('.btn-text').show();
                $('#saveCategoryBtn').find('.spinner-border').addClass('d-none');
                
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed to add category'
                });
            }
        });
    });
    
    // Edit category modal
    $(document).on('click', '.edit-category', function() {
        const categoryId = $(this).data('id');
        
        $.ajax({
            url: `/site-settings/categories/${categoryId}`,
            type: 'GET',
            success: function(response) {
                if (response.status === 200) {
                    $('#edit_category_id').val(response.category.id);
                    $('#edit_category_name').val(response.category.name);
                    $('#edit_category_description').val(response.category.description);
                    $('#editCategoryModal').modal('show');
                }
            },
            error: function(xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed to load category'
                });
            }
        });
    });
    
    // Update category
    $('#updateCategoryBtn').on('click', function() {
        $(this).find('.btn-text').hide();
        $(this).find('.spinner-border').removeClass('d-none');
        $('.is-invalid').removeClass('is-invalid');
        
        const categoryId = $('#edit_category_id').val();
        const categoryData = {
            name: $('#edit_category_name').val(),
            description: $('#edit_category_description').val()
        };
        
        $.ajax({
            url: `/site-settings/categories/${categoryId}`,
            type: 'PUT',
            data: categoryData,
            success: function(response) {
                $('#updateCategoryBtn').find('.btn-text').show();
                $('#updateCategoryBtn').find('.spinner-border').addClass('d-none');
                
                if (response.status === 400) {
                    handleValidationErrors(response.errors, 'edit_category_');
                    return;
                }
                
                if (response.status === 200) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: response.message
                    });
                    
                    $('#editCategoryModal').modal('hide');
                    loadCategories();
                }
            },
            error: function(xhr) {
                $('#updateCategoryBtn').find('.btn-text').show();
                $('#updateCategoryBtn').find('.spinner-border').addClass('d-none');
                
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed to update category'
                });
            }
        });
    });
    
    // Delete category modal
    $(document).on('click', '.delete-category', function() {
        const categoryId = $(this).data('id');
        $('#delete_category_id').val(categoryId);
        $('#deleteCategoryModal').modal('show');
    });
    
    // Confirm delete category
    $('#confirmDeleteCategoryBtn').on('click', function() {
        $(this).find('.btn-text').hide();
        $(this).find('.spinner-border').removeClass('d-none');
        
        const categoryId = $('#delete_category_id').val();
        
        $.ajax({
            url: `/site-settings/categories/${categoryId}`,
            type: 'DELETE',
            success: function(response) {
                $('#confirmDeleteCategoryBtn').find('.btn-text').show();
                $('#confirmDeleteCategoryBtn').find('.spinner-border').addClass('d-none');
                
                if (response.status === 200) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: response.message
                    });
                    
                    $('#deleteCategoryModal').modal('hide');
                    loadCategories();
                }
            },
            error: function(xhr) {
                $('#confirmDeleteCategoryBtn').find('.btn-text').show();
                $('#confirmDeleteCategoryBtn').find('.spinner-border').addClass('d-none');
                
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed to delete category'
                });
            }
        });
    });
    
    // ============== COUNTRY FUNCTIONS ==============
    
    // Load all countries
    function loadCountries() {
        $.ajax({
            url: '/site-settings/countries',
            type: 'GET',
            success: function(response) {
                if (response.status === 200) {
                    $('#countryTable tbody').empty();
                    
                    if (response.countries.length === 0) {
                        $('#countryTable tbody').html('<tr><td colspan="4" class="text-center">No countries found</td></tr>');
                        return;
                    }
                    
                    $.each(response.countries, function(index, country) {
                        // Extract category names for display
                        let categoryNames = '';
                        if (country.compatible_categories && country.compatible_categories.length > 0) {
                            categoryNames = country.compatible_categories.map(cat => cat.name).join(', ');
                        }

                        $('#countryTable tbody').append(`
                            <tr class="country-row" data-categories='${JSON.stringify(country.compatible_categories.map(c => c.id))}'>
                                <td>${index + 1}</td>
                                <td>${country.name}</td>
                                <td>${country.description || '-'}</td>
                                <td><span class="badge bg-info">${categoryNames || 'All Categories'}</span></td>
                                <td>
                                    <button class="btn btn-sm btn-info edit-country" data-id="${country.id}">
                                        <i class="bx bx-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger delete-country" data-id="${country.id}">
                                        <i class="bx bx-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        `);
                    });
                }
            },
            error: function(xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed to load countries'
                });
            }
        });
    }
    
    // Add country
    $('#saveCountryBtn').on('click', function() {
        $(this).find('.btn-text').hide();
        $(this).find('.spinner-border').removeClass('d-none');
        $('.is-invalid').removeClass('is-invalid');
        
        const countryData = {
            name: $('#country_name').val(),
            description: $('#country_description').val(),
            category_ids: $('#country_categories').val()
        };
        
        $.ajax({
            url: '/site-settings/countries',
            type: 'POST',
            data: countryData,
            success: function(response) {
                $('#saveCountryBtn').find('.btn-text').show();
                $('#saveCountryBtn').find('.spinner-border').addClass('d-none');
                
                if (response.status === 400) {
                    handleValidationErrors(response.errors, 'country_');
                    return;
                }
                
                if (response.status === 200) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: response.message
                    });
                    
                    $('#addCountryModal').modal('hide');
                    $('#addCountryForm')[0].reset();
                    loadCountries();
                }
            },
            error: function(xhr) {
                $('#saveCountryBtn').find('.btn-text').show();
                $('#saveCountryBtn').find('.spinner-border').addClass('d-none');
                
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed to add country'
                });
            }
        });
    });
    
    // Edit country modal
    $(document).on('click', '.edit-country', function() {
        const countryId = $(this).data('id');
        
        $.ajax({
            url: `/site-settings/countries/${countryId}`,
            type: 'GET',
            success: function(response) {
                if (response.status === 200) {
                    $('#edit_country_id').val(response.country.id);
                    $('#edit_country_name').val(response.country.name);
                    $('#edit_country_description').val(response.country.description);
                    
                    // Set selected categories
                    if (response.country.compatible_categories) {
                        const categoryIds = response.country.compatible_categories.map(cat => cat.id);
                        $('#edit_country_categories').val(categoryIds).trigger('change');
                    }
                    
                    $('#editCountryModal').modal('show');
                }
            },
            error: function(xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed to load country'
                });
            }
        });
    });
    
    // Update country
    $('#updateCountryBtn').on('click', function() {
        $(this).find('.btn-text').hide();
        $(this).find('.spinner-border').removeClass('d-none');
        $('.is-invalid').removeClass('is-invalid');
        
        const countryId = $('#edit_country_id').val();
        const countryData = {
            name: $('#edit_country_name').val(),
            description: $('#edit_country_description').val(),
            category_ids: $('#edit_country_categories').val()
        };
        
        $.ajax({
            url: `/site-settings/countries/${countryId}`,
            type: 'PUT',
            data: countryData,
            success: function(response) {
                $('#updateCountryBtn').find('.btn-text').show();
                $('#updateCountryBtn').find('.spinner-border').addClass('d-none');
                
                if (response.status === 400) {
                    handleValidationErrors(response.errors, 'edit_country_');
                    return;
                }
                
                if (response.status === 200) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: response.message
                    });
                    
                    $('#editCountryModal').modal('hide');
                    loadCountries();
                }
            },
            error: function(xhr) {
                $('#updateCountryBtn').find('.btn-text').show();
                $('#updateCountryBtn').find('.spinner-border').addClass('d-none');
                
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed to update country'
                });
            }
        });
    });
    
    // Delete country modal
    $(document).on('click', '.delete-country', function() {
        const countryId = $(this).data('id');
        $('#delete_country_id').val(countryId);
        $('#deleteCountryModal').modal('show');
    });
    
    // Confirm delete country
    $('#confirmDeleteCountryBtn').on('click', function() {
        $(this).find('.btn-text').hide();
        $(this).find('.spinner-border').removeClass('d-none');
        
        const countryId = $('#delete_country_id').val();
        
        $.ajax({
            url: `/site-settings/countries/${countryId}`,
            type: 'DELETE',
            success: function(response) {
                $('#confirmDeleteCountryBtn').find('.btn-text').show();
                $('#confirmDeleteCountryBtn').find('.spinner-border').addClass('d-none');
                
                if (response.status === 200) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: response.message
                    });
                    
                    $('#deleteCountryModal').modal('hide');
                    loadCountries();
                }
            },
            error: function(xhr) {
                $('#confirmDeleteCountryBtn').find('.btn-text').show();
                $('#confirmDeleteCountryBtn').find('.spinner-border').addClass('d-none');
                
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed to delete country'
                });
            }
        });
    });
    
    // ============== PURPOSE FUNCTIONS ==============
    
    // Load all purposes
    function loadPurposes() {
        $.ajax({
            url: '/site-settings/purposes',
            type: 'GET',
            success: function(response) {
                if (response.status === 200) {
                    $('#purposeTable tbody').empty();
                    
                    if (response.purposes.length === 0) {
                        $('#purposeTable tbody').html('<tr><td colspan="5" class="text-center">No purposes found</td></tr>');
                        return;
                    }
                    
                    $.each(response.purposes, function(index, purpose) {
                        // Extract category names for display
                        let categoryNames = '';
                        if (purpose.compatible_categories && purpose.compatible_categories.length > 0) {
                            categoryNames = purpose.compatible_categories.map(cat => cat.name).join(', ');
                        }

                        $('#purposeTable tbody').append(`
                            <tr class="purpose-row" data-categories='${JSON.stringify(purpose.compatible_categories.map(c => c.id))}'>
                                <td>${index + 1}</td>
                                <td>${purpose.name}</td>
                                <td>${purpose.description || '-'}</td>
                                <td><span class="badge bg-info">${categoryNames || 'All Categories'}</span></td>
                                <td>
                                    <button class="btn btn-sm btn-info edit-purpose" data-id="${purpose.id}">
                                        <i class="bx bx-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger delete-purpose" data-id="${purpose.id}">
                                        <i class="bx bx-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        `);
                    });
                }
            },
            error: function(xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed to load purposes'
                });
            }
        });
    }
    
    // Add purpose
    $('#savePurposeBtn').on('click', function() {
        $(this).find('.btn-text').hide();
        $(this).find('.spinner-border').removeClass('d-none');
        $('.is-invalid').removeClass('is-invalid');
        
        const purposeData = {
            name: $('#purpose_name').val(),
            description: $('#purpose_description').val(),
            category_ids: $('#purpose_categories').val()
        };
        
        $.ajax({
            url: '/site-settings/purposes',
            type: 'POST',
            data: purposeData,
            success: function(response) {
                $('#savePurposeBtn').find('.btn-text').show();
                $('#savePurposeBtn').find('.spinner-border').addClass('d-none');
                
                if (response.status === 400) {
                    handleValidationErrors(response.errors, 'purpose_');
                    return;
                }
                
                if (response.status === 200) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: response.message
                    });
                    
                    $('#addPurposeModal').modal('hide');
                    $('#addPurposeForm')[0].reset();
                    loadPurposes();
                }
            },
            error: function(xhr) {
                $('#savePurposeBtn').find('.btn-text').show();
                $('#savePurposeBtn').find('.spinner-border').addClass('d-none');
                
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed to add purpose'
                });
            }
        });
    });
    
    // Edit purpose modal
    $(document).on('click', '.edit-purpose', function() {
        const purposeId = $(this).data('id');
        
        $.ajax({
            url: `/site-settings/purposes/${purposeId}`,
            type: 'GET',
            success: function(response) {
                if (response.status === 200) {
                    $('#edit_purpose_id').val(response.purpose.id);
                    $('#edit_purpose_name').val(response.purpose.name);
                    $('#edit_purpose_description').val(response.purpose.description);
                    
                    // Set selected categories
                    if (response.purpose.compatible_categories) {
                        const categoryIds = response.purpose.compatible_categories.map(cat => cat.id);
                        $('#edit_purpose_categories').val(categoryIds).trigger('change');
                    }
                    
                    $('#editPurposeModal').modal('show');
                }
            },
            error: function(xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed to load purpose'
                });
            }
        });
    });
    
    // Update purpose
    $('#updatePurposeBtn').on('click', function() {
        $(this).find('.btn-text').hide();
        $(this).find('.spinner-border').removeClass('d-none');
        $('.is-invalid').removeClass('is-invalid');
        
        const purposeId = $('#edit_purpose_id').val();
        const purposeData = {
            name: $('#edit_purpose_name').val(),
            description: $('#edit_purpose_description').val(),
            category_ids: $('#edit_purpose_categories').val()
        };
        
        $.ajax({
            url: `/site-settings/purposes/${purposeId}`,
            type: 'PUT',
            data: purposeData,
            success: function(response) {
                $('#updatePurposeBtn').find('.btn-text').show();
                $('#updatePurposeBtn').find('.spinner-border').addClass('d-none');
                
                if (response.status === 400) {
                    handleValidationErrors(response.errors, 'edit_purpose_');
                    return;
                }
                
                if (response.status === 200) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: response.message
                    });
                    
                    $('#editPurposeModal').modal('hide');
                    loadPurposes();
                }
            },
            error: function(xhr) {
                $('#updatePurposeBtn').find('.btn-text').show();
                $('#updatePurposeBtn').find('.spinner-border').addClass('d-none');
                
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed to update purpose'
                });
            }
        });
    });
    
    // Delete purpose modal
    $(document).on('click', '.delete-purpose', function() {
        const purposeId = $(this).data('id');
        $('#delete_purpose_id').val(purposeId);
        $('#deletePurposeModal').modal('show');
    });
    
    // Confirm delete purpose
    $('#confirmDeletePurposeBtn').on('click', function() {
        $(this).find('.btn-text').hide();
        $(this).find('.spinner-border').removeClass('d-none');
        
        const purposeId = $('#delete_purpose_id').val();
        
        $.ajax({
            url: `/site-settings/purposes/${purposeId}`,
            type: 'DELETE',
            success: function(response) {
                $('#confirmDeletePurposeBtn').find('.btn-text').show();
                $('#confirmDeletePurposeBtn').find('.spinner-border').addClass('d-none');
                
                if (response.status === 200) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: response.message
                    });
                    
                    $('#deletePurposeModal').modal('hide');
                    loadPurposes();
                }
            },
            error: function(xhr) {
                $('#confirmDeletePurposeBtn').find('.btn-text').show();
                $('#confirmDeletePurposeBtn').find('.spinner-border').addClass('d-none');
                
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed to delete purpose'
                });
            }
        });
    });
    
    // ============== FEATURE FUNCTIONS ==============
    
    // Load all features
    function loadFeatures() {
        $.ajax({
            url: '/site-settings/features',
            type: 'GET',
            success: function(response) {
                if (response.status === 200) {
                    $('#featureTable tbody').empty();
                    
                    if (response.features.length === 0) {
                        $('#featureTable tbody').html('<tr><td colspan="6" class="text-center">No features found</td></tr>');
                        return;
                    }
                    
                    $.each(response.features, function(index, feature) {
                        // Extract category names for display
                        let categoryNames = '';
                        if (feature.compatible_categories && feature.compatible_categories.length > 0) {
                            categoryNames = feature.compatible_categories.map(cat => cat.name).join(', ');
                        }

                        $('#featureTable tbody').append(`
                            <tr class="feature-row" data-categories='${JSON.stringify(feature.compatible_categories.map(c => c.id))}'>
                                <td>${index + 1}</td>
                                <td>${feature.name}</td>
                                <td>${feature.description || '-'}</td>
                                <td>${feature.points}</td>
                                <td><span class="badge bg-info">${categoryNames || 'All Categories'}</span></td>
                                <td>
                                    <button class="btn btn-sm btn-info edit-feature" data-id="${feature.id}">
                                        <i class="bx bx-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger delete-feature" data-id="${feature.id}">
                                        <i class="bx bx-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        `);
                    });
                }
            },
            error: function(xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed to load features'
                });
            }
        });
    }
    
    // Add feature
    $('#saveFeatureBtn').on('click', function() {
        $(this).find('.btn-text').hide();
        $(this).find('.spinner-border').removeClass('d-none');
        $('.is-invalid').removeClass('is-invalid');
        
        const featureData = {
            name: $('#feature_name').val(),
            description: $('#feature_description').val(),
            points: $('#feature_points').val(),
            category_ids: $('#feature_categories').val()
        };
        
        $.ajax({
            url: '/site-settings/features',
            type: 'POST',
            data: featureData,
            success: function(response) {
                $('#saveFeatureBtn').find('.btn-text').show();
                $('#saveFeatureBtn').find('.spinner-border').addClass('d-none');
                
                if (response.status === 400) {
                    handleValidationErrors(response.errors, 'feature_');
                    return;
                }
                
                if (response.status === 200) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: response.message
                    });
                    
                    $('#addFeatureModal').modal('hide');
                    $('#addFeatureForm')[0].reset();
                    loadFeatures();
                }
            },
            error: function(xhr) {
                $('#saveFeatureBtn').find('.btn-text').show();
                $('#saveFeatureBtn').find('.spinner-border').addClass('d-none');
                
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed to add feature'
                });
            }
        });
    });
    
    // Edit feature modal
    $(document).on('click', '.edit-feature', function() {
        const featureId = $(this).data('id');
        
        $.ajax({
            url: `/site-settings/features/${featureId}`,
            type: 'GET',
            success: function(response) {
                if (response.status === 200) {
                    $('#edit_feature_id').val(response.feature.id);
                    $('#edit_feature_name').val(response.feature.name);
                    $('#edit_feature_description').val(response.feature.description);
                    $('#edit_feature_points').val(response.feature.points);
                    
                    // Set selected categories
                    if (response.feature.compatible_categories) {
                        const categoryIds = response.feature.compatible_categories.map(cat => cat.id);
                        $('#edit_feature_categories').val(categoryIds).trigger('change');
                    }
                    
                    $('#editFeatureModal').modal('show');
                }
            },
            error: function(xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed to load feature'
                });
            }
        });
    });
    
    // Update feature
    $('#updateFeatureBtn').on('click', function() {
        $(this).find('.btn-text').hide();
        $(this).find('.spinner-border').removeClass('d-none');
        $('.is-invalid').removeClass('is-invalid');
        
        const featureId = $('#edit_feature_id').val();
        const featureData = {
            name: $('#edit_feature_name').val(),
            description: $('#edit_feature_description').val(),
            points: $('#edit_feature_points').val(),
            category_ids: $('#edit_feature_categories').val()
        };
        
        $.ajax({
            url: `/site-settings/features/${featureId}`,
            type: 'PUT',
            data: featureData,
            success: function(response) {
                $('#updateFeatureBtn').find('.btn-text').show();
                $('#updateFeatureBtn').find('.spinner-border').addClass('d-none');
                
                if (response.status === 400) {
                    handleValidationErrors(response.errors, 'edit_feature_');
                    return;
                }
                
                if (response.status === 200) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: response.message
                    });
                    
                    $('#editFeatureModal').modal('hide');
                    loadFeatures();
                }
            },
            error: function(xhr) {
                $('#updateFeatureBtn').find('.btn-text').show();
                $('#updateFeatureBtn').find('.spinner-border').addClass('d-none');
                
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed to update feature'
                });
            }
        });
    });
    
    // Delete feature modal
    $(document).on('click', '.delete-feature', function() {
        const featureId = $(this).data('id');
        $('#delete_feature_id').val(featureId);
        $('#deleteFeatureModal').modal('show');
    });
    
    // Confirm delete feature
    $('#confirmDeleteFeatureBtn').on('click', function() {
        $(this).find('.btn-text').hide();
        $(this).find('.spinner-border').removeClass('d-none');
        
        const featureId = $('#delete_feature_id').val();
        
        $.ajax({
            url: `/site-settings/features/${featureId}`,
            type: 'DELETE',
            success: function(response) {
                $('#confirmDeleteFeatureBtn').find('.btn-text').show();
                $('#confirmDeleteFeatureBtn').find('.spinner-border').addClass('d-none');
                
                if (response.status === 200) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: response.message
                    });
                    
                    $('#deleteFeatureModal').modal('hide');
                    loadFeatures();
                }
            },
            error: function(xhr) {
                $('#confirmDeleteFeatureBtn').find('.btn-text').show();
                $('#confirmDeleteFeatureBtn').find('.spinner-border').addClass('d-none');
                
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed to delete feature'
                });
            }
        });
    });
    
    // ============== FILTERING FUNCTIONS ==============
    
    // Add filtering controls to each section
    function addFilteringControls() {
        // Add filter controls before each table
        const filterHtml = `
            <div class="mb-3">
                <label class="form-label">Filter by Category:</label>
                <select class="form-control category-filter">
                    <option value="all">All Categories</option>
                    <!-- Categories will be loaded dynamically -->
                </select>
            </div>
        `;
        
        // Add filter before country table
        $('#countryTable').before(filterHtml.replace('category-filter', 'country-category-filter'));
        
        // Add filter before purpose table
        $('#purposeTable').before(filterHtml.replace('category-filter', 'purpose-category-filter'));
        
        // Add filter before feature table
        $('#featureTable').before(filterHtml.replace('category-filter', 'feature-category-filter'));
        
        // Set up event handlers for filters
        $('.country-category-filter').on('change', filterCountries);
        $('.purpose-category-filter').on('change', filterPurposes);
        $('.feature-category-filter').on('change', filterFeatures);
    }
    
    // Filter countries by category
    function filterCountries() {
        const selectedCategory = $('.country-category-filter').val();
        
        if (selectedCategory === 'all') {
            // Show all rows
            $('.country-row').show();
        } else {
            // Filter rows
            $('.country-row').each(function() {
                const categoryIds = $(this).data('categories') || [];
                
                if (categoryIds.length === 0 || categoryIds.includes(parseInt(selectedCategory))) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        }
    }
    
    // Filter purposes by category
    function filterPurposes() {
        const selectedCategory = $('.purpose-category-filter').val();
        
        if (selectedCategory === 'all') {
            // Show all rows
            $('.purpose-row').show();
        } else {
            // Filter rows
            $('.purpose-row').each(function() {
                const categoryIds = $(this).data('categories') || [];
                
                if (categoryIds.length === 0 || categoryIds.includes(parseInt(selectedCategory))) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        }
    }
    
    // Filter features by category
    function filterFeatures() {
        const selectedCategory = $('.feature-category-filter').val();
        
        if (selectedCategory === 'all') {
            // Show all rows
            $('.feature-row').show();
        } else {
            // Filter rows
            $('.feature-row').each(function() {
                const categoryIds = $(this).data('categories') || [];
                
                if (categoryIds.length === 0 || categoryIds.includes(parseInt(selectedCategory))) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        }
    }
    
    // ============== UTILITY FUNCTIONS ==============
    
    // Initialize Select2 for category selector dropdowns
    function initializeSelect2() {
        // Convert regular select dropdowns to Select2
        $('.select2').select2({
            placeholder: 'Select compatible categories',
            allowClear: true,
            width: '100%'
        });
    }
    
    // Populate category dropdowns in all modals
    function populateCategoryDropdowns(categories) {
        // Clear existing options except the first placeholder
        $('.select2').each(function() {
            $(this).empty();
        });
        
        // Add categories to filter dropdowns
        $('.country-category-filter, .purpose-category-filter, .feature-category-filter').each(function() {
            const selectEl = $(this);
            selectEl.find('option:not(:first)').remove();
            
            categories.forEach(function(category) {
                selectEl.append(`<option value="${category.id}">${category.name}</option>`);
            });
        });
        
        // Add categories to form dropdowns
        $('#country_categories, #edit_country_categories, #purpose_categories, #edit_purpose_categories, #feature_categories, #edit_feature_categories').each(function() {
            const selectEl = $(this);
            
            categories.forEach(function(category) {
                selectEl.append(`<option value="${category.id}">${category.name}</option>`);
            });
        });
    }
    
    // Handle validation errors
    function handleValidationErrors(errors, prefix = '') {
        $.each(errors, function(field, messages) {
            const inputField = $(`#${prefix}${field}`);
            inputField.addClass('is-invalid');
            $(`#${prefix}${field}_error`).text(messages[0]);
        });
    }
    
    // Clear form data and errors when modal is closed
    $('.modal').on('hidden.bs.modal', function() {
        $(this).find('form')[0].reset();
        $('.is-invalid').removeClass('is-invalid');
        
        // Reset Select2 controls
        $(this).find('.select2').val(null).trigger('change');
    });
}); 
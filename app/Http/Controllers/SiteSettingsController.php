<?php

namespace App\Http\Controllers;

use App\Models\SiteCategory;
use App\Models\Country;
use App\Models\WorkPurpose;
use App\Models\SiteFeature;
use App\Models\Sites;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SiteSettingsController extends Controller
{
    /**
     * Display the site settings page
     */
    public function index()
    {
        return view('site_settings.sites_settings');
    }

    /**
     * Display all categories
     */
    public function getCategories()
    {
        $categories = SiteCategory::latest()->get();
        return response()->json([
            'status' => 200,
            'categories' => $categories,
        ]);
    }

    /**
     * Store a new category
     */
    public function storeCategory(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:191',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'errors' => $validator->messages(),
            ]);
        }

        $category = SiteCategory::create([
            'name' => $request->name,
            'description' => $request->description,
        ]);

        return response()->json([
            'status' => 200,
            'message' => 'Category created successfully!',
            'category' => $category,
        ]);
    }

    /**
     * Get a specific category
     */
    public function getCategory($id)
    {
        $category = SiteCategory::findOrFail($id);
        
        if ($category) {
            return response()->json([
                'status' => 200,
                'category' => $category,
            ]);
        } else {
            return response()->json([
                'status' => 404,
                'message' => 'Category not found',
            ]);
        }
    }

    /**
     * Update a category
     */
    public function updateCategory(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:191',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'errors' => $validator->messages(),
            ]);
        }

        $category = SiteCategory::findOrFail($id);
        
        if ($category) {
            $category->update([
                'name' => $request->name,
                'description' => $request->description,
            ]);
            
            return response()->json([
                'status' => 200,
                'message' => 'Category updated successfully!',
                'category' => $category,
            ]);
        } else {
            return response()->json([
                'status' => 404,
                'message' => 'Category not found',
            ]);
        }
    }

    /**
     * Delete a category
     */
    public function deleteCategory($id)
    {
        $category = SiteCategory::findOrFail($id);
        
        if ($category) {
            $category->delete();
            
            return response()->json([
                'status' => 200,
                'message' => 'Category deleted successfully!',
            ]);
        } else {
            return response()->json([
                'status' => 404,
                'message' => 'Category not found',
            ]);
        }
    }

    /**
     * Display all countries
     */
    public function getCountries()
    {
        $countries = Country::latest()->get();
        return response()->json([
            'status' => 200,
            'countries' => $countries,
        ]);
    }

    /**
     * Store a new country
     */
    public function storeCountry(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:191',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'errors' => $validator->messages(),
            ]);
        }

        $country = Country::create([
            'name' => $request->name,
            'description' => $request->description,
        ]);

        return response()->json([
            'status' => 200,
            'message' => 'Country created successfully!',
            'country' => $country,
        ]);
    }

    /**
     * Get a specific country
     */
    public function getCountry($id)
    {
        $country = Country::findOrFail($id);
        
        if ($country) {
            return response()->json([
                'status' => 200,
                'country' => $country,
            ]);
        } else {
            return response()->json([
                'status' => 404,
                'message' => 'Country not found',
            ]);
        }
    }

    /**
     * Update a country
     */
    public function updateCountry(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:191',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'errors' => $validator->messages(),
            ]);
        }

        $country = Country::findOrFail($id);
        
        if ($country) {
            $country->update([
                'name' => $request->name,
                'description' => $request->description,
            ]);
            
            return response()->json([
                'status' => 200,
                'message' => 'Country updated successfully!',
                'country' => $country,
            ]);
        } else {
            return response()->json([
                'status' => 404,
                'message' => 'Country not found',
            ]);
        }
    }

    /**
     * Delete a country
     */
    public function deleteCountry($id)
    {
        $country = Country::findOrFail($id);
        
        if ($country) {
            $country->delete();
            
            return response()->json([
                'status' => 200,
                'message' => 'Country deleted successfully!',
            ]);
        } else {
            return response()->json([
                'status' => 404,
                'message' => 'Country not found',
            ]);
        }
    }

    /**
     * Display all work purposes
     */
    public function getPurposes()
    {
        $purposes = WorkPurpose::latest()->get();
        return response()->json([
            'status' => 200,
            'purposes' => $purposes,
        ]);
    }

    /**
     * Store a new purpose
     */
    public function storePurpose(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:191',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'errors' => $validator->messages(),
            ]);
        }

        $purpose = WorkPurpose::create([
            'name' => $request->name,
            'description' => $request->description,
        ]);

        return response()->json([
            'status' => 200,
            'message' => 'Purpose created successfully!',
            'purpose' => $purpose,
        ]);
    }

    /**
     * Get a specific purpose
     */
    public function getPurpose($id)
    {
        $purpose = WorkPurpose::findOrFail($id);
        
        if ($purpose) {
            return response()->json([
                'status' => 200,
                'purpose' => $purpose,
            ]);
        } else {
            return response()->json([
                'status' => 404,
                'message' => 'Purpose not found',
            ]);
        }
    }

    /**
     * Update a purpose
     */
    public function updatePurpose(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:191',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'errors' => $validator->messages(),
            ]);
        }

        $purpose = WorkPurpose::findOrFail($id);
        
        if ($purpose) {
            $purpose->update([
                'name' => $request->name,
                'description' => $request->description,
            ]);
            
            return response()->json([
                'status' => 200,
                'message' => 'Purpose updated successfully!',
                'purpose' => $purpose,
            ]);
        } else {
            return response()->json([
                'status' => 404,
                'message' => 'Purpose not found',
            ]);
        }
    }

    /**
     * Delete a purpose
     */
    public function deletePurpose($id)
    {
        $purpose = WorkPurpose::findOrFail($id);
        
        if ($purpose) {
            $purpose->delete();
            
            return response()->json([
                'status' => 200,
                'message' => 'Purpose deleted successfully!',
            ]);
        } else {
            return response()->json([
                'status' => 404,
                'message' => 'Purpose not found',
            ]);
        }
    }

    /**
     * Display all features
     */
    public function getFeatures()
    {
        $features = SiteFeature::latest()->get();
        return response()->json([
            'status' => 200,
            'features' => $features,
        ]);
    }

    /**
     * Store a new feature
     */
    public function storeFeature(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:191',
            'description' => 'nullable|string',
            'points' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'errors' => $validator->messages(),
            ]);
        }

        $feature = SiteFeature::create([
            'name' => $request->name,
            'description' => $request->description,
            'points' => $request->points,
        ]);

        return response()->json([
            'status' => 200,
            'message' => 'Feature created successfully!',
            'feature' => $feature,
        ]);
    }

    /**
     * Get a specific feature
     */
    public function getFeature($id)
    {
        $feature = SiteFeature::findOrFail($id);
        
        if ($feature) {
            return response()->json([
                'status' => 200,
                'feature' => $feature,
            ]);
        } else {
            return response()->json([
                'status' => 404,
                'message' => 'Feature not found',
            ]);
        }
    }

    /**
     * Update a feature
     */
    public function updateFeature(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:191',
            'description' => 'nullable|string',
            'points' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'errors' => $validator->messages(),
            ]);
        }

        $feature = SiteFeature::findOrFail($id);
        
        if ($feature) {
            $feature->update([
                'name' => $request->name,
                'description' => $request->description,
                'points' => $request->points,
            ]);
            
            return response()->json([
                'status' => 200,
                'message' => 'Feature updated successfully!',
                'feature' => $feature,
            ]);
        } else {
            return response()->json([
                'status' => 404,
                'message' => 'Feature not found',
            ]);
        }
    }

    /**
     * Delete a feature
     */
    public function deleteFeature($id)
    {
        $feature = SiteFeature::findOrFail($id);
        
        if ($feature) {
            $feature->delete();
            
            return response()->json([
                'status' => 200,
                'message' => 'Feature deleted successfully!',
            ]);
        } else {
            return response()->json([
                'status' => 404,
                'message' => 'Feature not found',
            ]);
        }
    }

    /**
     * Update site rating based on features
     */
    public function updateSiteRating(Request $request, $siteId)
    {
        $validator = Validator::make($request->all(), [
            'features' => 'required|array',
            'features.*' => 'exists:site_features,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'errors' => $validator->messages(),
            ]);
        }

        $site = Sites::findOrFail($siteId);
        
        if (!$site) {
            return response()->json([
                'status' => 404,
                'message' => 'Site not found',
            ]);
        }

        // Get all features
        $allFeatures = SiteFeature::all();
        
        // Sync all features first (set has_feature to false)
        $syncData = [];
        foreach ($allFeatures as $feature) {
            $syncData[$feature->id] = ['has_feature' => false];
        }
        
        // Then update the ones that are selected
        foreach ($request->features as $featureId) {
            $syncData[$featureId] = ['has_feature' => true];
        }
        
        // Sync the data
        $site->features()->sync($syncData);
        
        // Calculate new rating
        $site->calculateRating();
        
        return response()->json([
            'status' => 200,
            'message' => 'Site rating updated successfully!',
            'rating' => $site->rating,
            'max_rating' => $site->max_rating,
        ]);
    }

    /**
     * Get all settings data for site creation
     */
    public function getSettingsData()
    {
        $categories = SiteCategory::all();
        $countries = Country::all();
        $purposes = WorkPurpose::all();
        $features = SiteFeature::all();
        
        return response()->json([
            'status' => 200,
            'categories' => $categories,
            'countries' => $countries,
            'purposes' => $purposes,
            'features' => $features,
        ]);
    }
} 
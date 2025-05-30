<?php

namespace App\Http\Controllers;

use App\Models\SiteCategory;
use App\Models\Country;
use App\Models\WorkPurpose;
use App\Models\SiteFeature;
use App\Models\Sites;
use App\Models\SiteSetting;
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
        $countries = Country::with('compatibleCategories')->latest()->get();
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
            'category_ids' => 'nullable|array',
            'category_ids.*' => 'exists:site_categories,id',
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
        
        // Sync compatible categories
        if ($request->has('category_ids')) {
            $country->compatibleCategories()->sync($request->category_ids);
        }

        return response()->json([
            'status' => 200,
            'message' => 'Country created successfully!',
            'country' => $country->load('compatibleCategories'),
        ]);
    }

    /**
     * Get a specific country
     */
    public function getCountry($id)
    {
        $country = Country::with('compatibleCategories')->findOrFail($id);
        
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
            'category_ids' => 'nullable|array',
            'category_ids.*' => 'exists:site_categories,id',
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
            
            // Sync compatible categories
            if ($request->has('category_ids')) {
                $country->compatibleCategories()->sync($request->category_ids);
            }
            
            return response()->json([
                'status' => 200,
                'message' => 'Country updated successfully!',
                'country' => $country->load('compatibleCategories'),
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
        $purposes = WorkPurpose::with('compatibleCategories')->latest()->get();
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
            'category_ids' => 'nullable|array',
            'category_ids.*' => 'exists:site_categories,id',
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
        
        // Sync compatible categories
        if ($request->has('category_ids')) {
            $purpose->compatibleCategories()->sync($request->category_ids);
        }

        return response()->json([
            'status' => 200,
            'message' => 'Purpose created successfully!',
            'purpose' => $purpose->load('compatibleCategories'),
        ]);
    }

    /**
     * Get a specific purpose
     */
    public function getPurpose($id)
    {
        $purpose = WorkPurpose::with('compatibleCategories')->findOrFail($id);
        
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
            'category_ids' => 'nullable|array',
            'category_ids.*' => 'exists:site_categories,id',
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
            
            // Sync compatible categories
            if ($request->has('category_ids')) {
                $purpose->compatibleCategories()->sync($request->category_ids);
            }
            
            return response()->json([
                'status' => 200,
                'message' => 'Purpose updated successfully!',
                'purpose' => $purpose->load('compatibleCategories'),
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
        $features = SiteFeature::with('compatibleCategories')->latest()->get();
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
            'category_ids' => 'nullable|array',
            'category_ids.*' => 'exists:site_categories,id',
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
        
        // Sync compatible categories
        if ($request->has('category_ids')) {
            $feature->compatibleCategories()->sync($request->category_ids);
        }

        return response()->json([
            'status' => 200,
            'message' => 'Feature created successfully!',
            'feature' => $feature->load('compatibleCategories'),
        ]);
    }

    /**
     * Get a specific feature
     */
    public function getFeature($id)
    {
        $feature = SiteFeature::with('compatibleCategories')->findOrFail($id);
        
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
            'category_ids' => 'nullable|array',
            'category_ids.*' => 'exists:site_categories,id',
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
            
            // Sync compatible categories
            if ($request->has('category_ids')) {
                $feature->compatibleCategories()->sync($request->category_ids);
            }
            
            return response()->json([
                'status' => 200,
                'message' => 'Feature updated successfully!',
                'feature' => $feature->load('compatibleCategories'),
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
    
    /**
     * Get compatible countries for a specific category
     */
    public function getCompatibleCountries($categoryId)
    {
        $category = SiteCategory::findOrFail($categoryId);
        $compatibleCountries = $category->compatibleCountries;
        
        return response()->json([
            'status' => 200,
            'compatibleCountries' => $compatibleCountries,
        ]);
    }
    
    /**
     * Get compatible purposes for a specific category
     */
    public function getCompatiblePurposes($categoryId)
    {
        $category = SiteCategory::findOrFail($categoryId);
        $compatiblePurposes = $category->compatibleWorkPurposes;
        
        return response()->json([
            'status' => 200,
            'compatiblePurposes' => $compatiblePurposes,
        ]);
    }
    
    /**
     * Get compatible features for a specific category
     */
    public function getCompatibleFeatures($categoryId)
    {
        $category = SiteCategory::with('compatibleFeatures')->findOrFail($categoryId);
        
        if ($category) {
            return response()->json([
                'status' => 200,
                'features' => $category->compatibleFeatures,
            ]);
        } else {
            return response()->json([
                'status' => 404,
                'message' => 'Category not found',
            ]);
        }
    }
    
    /**
     * Get rating settings
     */
    public function getRatingSettings()
    {
        $settings = SiteSetting::getGroup('rating');
        
        // Format settings into a more usable structure
        $formattedSettings = [];
        foreach ($settings as $setting) {
            $formattedSettings[$setting->key] = $setting->value;
        }
        
        // Set defaults if not found
        if (!isset($formattedSettings['rating_scale'])) $formattedSettings['rating_scale'] = 10;
        if (!isset($formattedSettings['rating_threshold_high'])) $formattedSettings['rating_threshold_high'] = 7;
        if (!isset($formattedSettings['rating_threshold_medium'])) $formattedSettings['rating_threshold_medium'] = 4;
        if (!isset($formattedSettings['rating_display_decimal_places'])) $formattedSettings['rating_display_decimal_places'] = 1;
        
        return response()->json([
            'status' => 200,
            'settings' => $formattedSettings,
            'rating_settings' => [
                'scale' => (float) $formattedSettings['rating_scale'],
                'thresholdHigh' => (float) $formattedSettings['rating_threshold_high'],
                'thresholdMedium' => (float) $formattedSettings['rating_threshold_medium'],
                'decimalPlaces' => (int) $formattedSettings['rating_display_decimal_places']
            ]
        ]);
    }
    
    /**
     * Update rating settings
     */
    public function updateRatingSettings(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'rating_scale' => 'required|numeric|min:1|max:100',
            'rating_threshold_high' => 'required|numeric|min:0',
            'rating_threshold_medium' => 'required|numeric|min:0',
            'rating_display_decimal_places' => 'required|integer|min:0|max:5',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'errors' => $validator->messages(),
            ]);
        }
        
        // Ensure thresholds are logical
        $ratingScale = (float) $request->input('rating_scale');
        $thresholdHigh = (float) $request->input('rating_threshold_high');
        $thresholdMedium = (float) $request->input('rating_threshold_medium');
        
        if ($thresholdHigh > $ratingScale) {
            return response()->json([
                'status' => 400,
                'errors' => [
                    'rating_threshold_high' => ['High threshold cannot be greater than the maximum rating scale']
                ],
            ]);
        }
        
        if ($thresholdMedium > $thresholdHigh) {
            return response()->json([
                'status' => 400,
                'errors' => [
                    'rating_threshold_medium' => ['Medium threshold cannot be greater than the high threshold']
                ],
            ]);
        }
        
        // Update settings
        SiteSetting::set(
            'rating_scale', 
            $ratingScale, 
            'rating', 
            'The maximum value for normalized ratings', 
            true
        );
        
        SiteSetting::set(
            'rating_threshold_high', 
            $thresholdHigh, 
            'rating', 
            'Threshold for high ratings', 
            true
        );
        
        SiteSetting::set(
            'rating_threshold_medium', 
            $thresholdMedium, 
            'rating', 
            'Threshold for medium ratings', 
            true
        );
        
        SiteSetting::set(
            'rating_display_decimal_places', 
            (int) $request->input('rating_display_decimal_places'), 
            'rating', 
            'Number of decimal places to display in ratings', 
            true
        );
        
        // Optionally recalculate all site ratings
        if ($request->input('recalculate_all_ratings', false)) {
            $sites = Sites::all();
            foreach ($sites as $site) {
                $site->calculateRating();
            }
        }
        
        return response()->json([
            'status' => 200,
            'message' => 'Rating settings updated successfully!',
            'rating_settings' => [
                'scale' => $ratingScale,
                'thresholdHigh' => $thresholdHigh,
                'thresholdMedium' => $thresholdMedium,
                'decimalPlaces' => (int) $request->input('rating_display_decimal_places')
            ]
        ]);
    }
} 
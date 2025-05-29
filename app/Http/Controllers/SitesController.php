<?php

namespace App\Http\Controllers;
use App\Models\Sites;
use App\Models\SiteCategory;
use App\Models\Country;
use App\Models\WorkPurpose;
use App\Models\SiteFeature;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SitesController extends Controller
{
    public function sites()
    {
        $categories = SiteCategory::all();
        $countries = Country::all();
        $purposes = WorkPurpose::all();
        $features = SiteFeature::all();
        
        return view('sites.index', compact('categories', 'countries', 'purposes', 'features'));
    }

    public function storeSite(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'        => 'required|max:191',
            'url'         => 'required|url|max:191',
            'description' => 'nullable|string',
            'status'      => 'required|in:active,inactive',
            'type'        => 'required|in:general,blog,shop,portfolio',
            'theme'       => 'required|string|max:191',
            'categories'  => 'nullable|array',
            'categories.*' => 'exists:site_categories,id',
            'countries'   => 'nullable|array',
            'countries.*' => 'exists:countries,id',
            'is_global'   => 'nullable|boolean',
            'purposes'    => 'nullable|array',
            'purposes.*'  => 'exists:work_purposes,id',
            'features'    => 'nullable|array',
            'features.*'  => 'exists:site_features,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'errors' => $validator->messages(),
            ]);
        }

        // Create site using mass assignment
        $site = Sites::create([
            'name'        => $request->input('name'),
            'url'         => $request->input('url'),
            'description' => $request->input('description'),
            'status'      => $request->input('status'),
            'type'        => $request->input('type'),
            'theme'       => $request->input('theme'),
        ]);

        // Assign categories if they exist
        if ($request->has('categories') && is_array($request->categories)) {
            $site->categories()->attach($request->categories);
        }

        // Handle countries - now allowing both global flag and specific countries
        if ($request->has('countries') && is_array($request->countries)) {
            $countrySyncData = [];
            foreach($request->countries as $countryId) {
                $countrySyncData[$countryId] = ['is_global' => $request->input('is_global') == true];
            }
            $site->countries()->attach($countrySyncData);
        } elseif ($request->input('is_global') == true) {
            // If only global flag is set but no countries selected, attach to first country
            $country = Country::first();
            if ($country) {
                $site->countries()->attach($country->id, ['is_global' => true]);
            }
        }

        // Assign work purposes if they exist
        if ($request->has('purposes') && is_array($request->purposes)) {
            $site->workPurposes()->attach($request->purposes);
        }

        // Assign features if they exist
        if ($request->has('features') && is_array($request->features)) {
            // Get all features
            $allFeatures = SiteFeature::all();
            
            // Set up sync data
            $syncData = [];
            foreach ($allFeatures as $feature) {
                $syncData[$feature->id] = ['has_feature' => in_array($feature->id, $request->features)];
            }
            
            $site->features()->sync($syncData);
            
            // Calculate rating
            $site->calculateRating();
        }

        return response()->json([
            'status' => 200,
            'message' => 'Site created successfully!',
            'site' => $site,
        ]);
    }

    public function fetchSite(Request $request)
    {
        // Start with a base query
        $query = Sites::with(['categories', 'countries', 'workPurposes', 'features']);
        
        // Filter by categories if specified
        if ($request->has('categories') && is_array($request->categories) && count($request->categories) > 0) {
            $query->whereHas('categories', function($q) use ($request) {
                $q->whereIn('site_categories.id', $request->categories);
            });
        }
        
        // Filter by countries, now handling both global and specific countries
        $hasCountryFilter = false;
        
        // Include global sites if global flag is set
        if ($request->input('is_global') == 1) {
            $hasGlobalQuery = clone $query;
            $hasGlobalQuery->whereHas('countries', function($q) {
                $q->where('is_global', true);
            });
            $hasCountryFilter = true;
        }
        
        // Include sites for specific countries if countries are selected
        if ($request->has('countries') && is_array($request->countries) && count($request->countries) > 0) {
            if (isset($hasGlobalQuery)) {
                // We have both global and specific countries, use a separate query for countries
                $hasCountryQuery = clone $query;
                $hasCountryQuery->whereHas('countries', function($q) use ($request) {
                    $q->whereIn('countries.id', $request->countries);
                });
                
                // Merge the results with global query using union
                $query = $hasGlobalQuery->union($hasCountryQuery);
            } else {
                // Just filter by countries
                $query->whereHas('countries', function($q) use ($request) {
                    $q->whereIn('countries.id', $request->countries);
                });
            }
            $hasCountryFilter = true;
        } else if (isset($hasGlobalQuery)) {
            // If we only have global filter
            $query = $hasGlobalQuery;
        }
        
        // Filter by work purposes
        if ($request->has('purposes') && is_array($request->purposes) && count($request->purposes) > 0) {
            $query->whereHas('workPurposes', function($q) use ($request) {
                $q->whereIn('work_purposes.id', $request->purposes);
            });
        }
        
        // Filter by minimum rating
        if ($request->has('min_rating') && is_numeric($request->min_rating) && $request->min_rating > 0) {
            $query->where('rating', '>=', $request->min_rating);
        }
        
        // Sort by rating if requested
        if ($request->input('sort_by_rating') == 1) {
            $query->orderByDesc('rating');
        } else {
            $query->orderBy('name');
        }
        
        $sites = $query->get();
        
        // Transform each site to include simplified relationship data
        $sites = $sites->map(function ($site) {
            $siteData = $site->toArray();
            $siteData['categories_list'] = $site->categories->pluck('name')->join(', ');
            $siteData['is_global'] = $site->isGlobal();
            $siteData['countries_list'] = $site->countryList();
            $siteData['purposes_list'] = $site->workPurposes->pluck('name')->join(', ');
            return $siteData;
        });
        
        return response()->json([
            'sites' => $sites,
        ]);
    }

    public function editSite($id)
    {
        $site = Sites::with(['categories', 'countries', 'workPurposes', 'features'])->findOrFail($id);
        
        if($site){
            // Get categories
            $siteCategories = $site->categories->pluck('id')->toArray();
            
            // Get countries and check if global
            $isGlobal = $site->isGlobal();
            $siteCountries = $isGlobal ? [] : $site->countries->pluck('id')->toArray();
            
            // Get work purposes
            $sitePurposes = $site->workPurposes->pluck('id')->toArray();
            
            // Get site features
            $siteFeatures = $site->features()->wherePivot('has_feature', true)->pluck('id')->toArray();
            
            return response()->json([
                'status' => 200,
                'site' => $site,
                'site_categories' => $siteCategories,
                'is_global' => $isGlobal,
                'site_countries' => $siteCountries,
                'site_purposes' => $sitePurposes,
                'site_features' => $siteFeatures,
            ]);
        } else {
            return response()->json([
                'status' => 404,
                'message' => 'Site not found',
            ]);
        }
    }

    public function updateSite(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name'        => 'required|max:191',
            'url'         => 'required|url|max:191',
            'description' => 'nullable|string',
            'status'      => 'required|in:active,inactive',
            'type'        => 'required|in:general,blog,shop,portfolio',
            'theme'       => 'required|string|max:191',
            'categories'  => 'nullable|array',
            'categories.*' => 'exists:site_categories,id',
            'countries'   => 'nullable|array',
            'countries.*' => 'exists:countries,id',
            'is_global'   => 'nullable|boolean',
            'purposes'    => 'nullable|array',
            'purposes.*'  => 'exists:work_purposes,id',
            'features'    => 'nullable|array',
            'features.*'  => 'exists:site_features,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'errors' => $validator->messages(),
            ]);
        }

        $site = Sites::findOrFail($id);
        if ($site) {
            $site->update([
                'name'        => $request->input('name'),
                'url'         => $request->input('url'),
                'description' => $request->input('description'),
                'status'      => $request->input('status'),
                'type'        => $request->input('type'),
                'theme'       => $request->input('theme'),
            ]);
            
            // Update categories
            if ($request->has('categories')) {
                $site->categories()->sync($request->categories);
            }
            
            // Handle countries - allowing both global and specific countries
            $site->countries()->detach(); // Remove all existing countries first
            
            if ($request->has('countries') && is_array($request->countries) && count($request->countries) > 0) {
                $countrySyncData = [];
                foreach($request->countries as $countryId) {
                    $countrySyncData[$countryId] = ['is_global' => $request->input('is_global') == true];
                }
                $site->countries()->attach($countrySyncData);
            } elseif ($request->input('is_global') == true) {
                // If only global flag is set but no countries selected, attach to first country
                $country = Country::first();
                if ($country) {
                    $site->countries()->attach($country->id, ['is_global' => true]);
                }
            }
            
            // Update work purposes
            if ($request->has('purposes')) {
                $site->workPurposes()->sync($request->purposes);
            }
            
            // Update features if they exist
            if ($request->has('features') && is_array($request->features)) {
                // Get all features
                $allFeatures = SiteFeature::all();
                
                // Set up sync data
                $syncData = [];
                foreach ($allFeatures as $feature) {
                    $syncData[$feature->id] = ['has_feature' => in_array($feature->id, $request->features)];
                }
                
                $site->features()->sync($syncData);
                
                // Calculate rating
                $site->calculateRating();
            }
            
            return response()->json([
                'status' => 200,
                'message' => 'Site updated successfully!',
                'site' => $site,
            ]);
        } else {
            return response()->json([
                'status' => 404,
                'message' => 'Site not found',
            ]);
        }
    }

    public function destroySite($id)
    {
        // No changes needed here, as the deletion cascades to the pivot tables
        $site = Sites::findOrFail($id);
        $site->delete();
        
        return response()->json([
            'status' => 200,
            'message' => 'Site deleted successfully!',
        ]);
    }

    /**
     * Get compatible options based on selected categories
     */
    public function getCompatibleOptions(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'categories' => 'required|array',
            'categories.*' => 'exists:site_categories,id',
            'option_type' => 'required|in:countries,purposes,features',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'errors' => $validator->messages(),
            ]);
        }

        $categories = SiteCategory::whereIn('id', $request->categories)->get();
        $compatibleOptions = [];
        
        // If no categories found, return empty array
        if ($categories->isEmpty()) {
            return response()->json([
                'compatible_options' => [],
            ]);
        }
        
        switch ($request->option_type) {
            case 'countries':
                // Get countries that are compatible with ALL selected categories
                $query = Country::query();
                foreach ($categories as $category) {
                    $query->whereHas('compatibleCategories', function ($q) use ($category) {
                        $q->where('site_categories.id', $category->id);
                    });
                }
                $compatibleOptions = $query->pluck('id')->toArray();
                break;
                
            case 'purposes':
                // Get purposes that are compatible with ALL selected categories
                $query = WorkPurpose::query();
                foreach ($categories as $category) {
                    $query->whereHas('compatibleCategories', function ($q) use ($category) {
                        $q->where('site_categories.id', $category->id);
                    });
                }
                $compatibleOptions = $query->pluck('id')->toArray();
                break;
                
            case 'features':
                // Get features that are compatible with ALL selected categories
                $query = SiteFeature::query();
                foreach ($categories as $category) {
                    $query->whereHas('compatibleCategories', function ($q) use ($category) {
                        $q->where('site_categories.id', $category->id);
                    });
                }
                $compatibleOptions = $query->pluck('id')->toArray();
                break;
        }
        
        return response()->json([
            'compatible_options' => $compatibleOptions,
        ]);
    }
}

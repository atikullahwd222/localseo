<?php

namespace App\Http\Controllers;
use App\Models\Sites;
use App\Models\SiteCategory;
use App\Models\Country;
use App\Models\WorkPurpose;
use App\Models\SiteFeature;
use App\Models\SiteSetting;
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
        
        // Get 20 sites for default display with pagination
        $sites = Sites::with(['categories', 'countries', 'workPurposes', 'features'])
                    ->orderBy('created_at', 'desc')
                    ->paginate(20);
        
        // Transform each site to include simplified relationship data
        $sites->getCollection()->transform(function ($site) {
            $siteData = $site->toArray();
            $siteData['categories_list'] = $site->categories->pluck('name')->join(', ');
            $siteData['is_global'] = $site->isGlobal();
            $siteData['countries_list'] = $site->countryList();
            $siteData['purposes_list'] = $site->workPurposes->pluck('name')->join(', ');
            return $siteData;
        });
        
        return view('sites.index', compact('categories', 'countries', 'purposes', 'features', 'sites'));
    }

    /**
     * Check if a domain is reachable
     * 
     * @param string $domain
     * @return bool
     */
    private function isDomainReachable($domain)
    {
        try {
            // Clean up the domain (remove any protocol, path, or query string)
            $cleanDomain = preg_replace('#^https?://#', '', $domain);
            $cleanDomain = strtok($cleanDomain, '/'); // Remove any path
            
            // Try HTTPS first
            $isReachable = $this->checkUrl("https://" . $cleanDomain);
            
            // If not reachable via HTTPS, try HTTP
            if (!$isReachable) {
                $isReachable = $this->checkUrl("http://" . $cleanDomain);
            }
            
            // If still not reachable, try DNS lookup as a fallback
            if (!$isReachable) {
                $isReachable = $this->checkDnsRecord($cleanDomain);
            }
            
            return $isReachable;
        } catch (\Exception $e) {
            \Log::error("Error checking domain reachability for {$domain}: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Helper method to check a specific URL
     * 
     * @param string $url
     * @return bool
     */
    private function checkUrl($url)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36');
        
        curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if (!empty($error)) {
            \Log::warning("cURL error for {$url}: {$error}");
        }
        
        // Consider 2xx, 3xx, and some 4xx codes as reachable (site exists but may be returning errors)
        return $httpCode >= 200 && $httpCode < 500;
    }

    /**
     * Check if domain has valid DNS records
     * 
     * @param string $domain
     * @return bool
     */
    private function checkDnsRecord($domain)
    {
        // Check for A record (IPv4)
        $hasARecord = checkdnsrr($domain, 'A');
        
        // Check for AAAA record (IPv6)
        $hasAAAARecord = checkdnsrr($domain, 'AAAA');
        
        // Check for MX record (Mail)
        $hasMXRecord = checkdnsrr($domain, 'MX');
        
        // Return true if any of the records exist
        return $hasARecord || $hasAAAARecord || $hasMXRecord;
    }

    public function storeSite(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'url'         => [
                'required',
                'string',
                'max:191',
                'regex:/^(([a-zA-Z0-9]|[a-zA-Z0-9][a-zA-Z0-9\-]*[a-zA-Z0-9])\.)+([A-Za-z0-9]|[A-Za-z0-9][A-Za-z0-9\-]*[A-Za-z0-9])$/', // Allow domains with subdomains
                function ($attribute, $value, $fail) {
                    // Check if domain already exists
                    $exists = Sites::where('url', $value)->exists();
                    if ($exists) {
                        $fail('This domain is already registered in the system.');
                    }
                },
            ],
            'da'          => 'nullable|integer|min:0|max:100',
            'description' => 'nullable|string',
            'video_link'  => 'nullable|string|url',
            'status'      => 'required|in:Live,Pending',
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
        
        // Check if the domain is reachable
        $domain = $request->input('url');
        $isReachable = $this->isDomainReachable($domain);
        $serverStatus = $isReachable ? 'Online' : 'Offline';
        
        // If site is not reachable and status was set to Live, change it to Pending
        $status = $request->input('status');
        if (!$isReachable && $status === 'Live') {
            $status = 'Pending';
        }

        // Create site using mass assignment
        $site = Sites::create([
            'name'         => $request->input('url'), // Use the URL as the name
            'url'          => $domain,
            'complete_url' => $request->input('complete_url'),
            'da'           => $request->input('da'),
            'description'  => $request->input('description'),
            'video_link'   => $request->input('video_link'),
            'status'       => $status,
            'server_status' => $serverStatus,
            'type'         => $request->input('type'),
            'theme'        => $request->input('theme'),
        ]);

        // Assign categories if they exist
        if ($request->has('categories') && is_array($request->categories)) {
            // Ensure we don't have duplicate categories by using array_unique
            $uniqueCategories = array_unique($request->categories);
            $site->categories()->attach($uniqueCategories);
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
            // Ensure we don't have duplicate purposes by using array_unique
            $uniquePurposes = array_unique($request->purposes);
            $site->workPurposes()->attach($uniquePurposes);
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
        try {
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
                // Create a query for global sites
                $globalQuery = Sites::with(['categories', 'countries', 'workPurposes', 'features'])
                    ->whereHas('countries', function($q) {
                        $q->where('is_global', true);
                    });
                
                // Apply same category filter if it was specified
                if ($request->has('categories') && is_array($request->categories) && count($request->categories) > 0) {
                    $globalQuery->whereHas('categories', function($q) use ($request) {
                        $q->whereIn('site_categories.id', $request->categories);
                    });
                }
                
                $hasCountryFilter = true;
                $hasGlobalQuery = $globalQuery;
            }
            
            // Include sites for specific countries if countries are selected
            if ($request->has('countries') && is_array($request->countries) && count($request->countries) > 0) {
                if (isset($hasGlobalQuery)) {
                    // We have both global and specific countries
                    // Create a new query for country-specific sites
                    $countryQuery = Sites::with(['categories', 'countries', 'workPurposes', 'features'])
                        ->whereHas('countries', function($q) use ($request) {
                            $q->whereIn('countries.id', $request->countries);
                        });
                    
                    // Apply same category filter if it was specified
                    if ($request->has('categories') && is_array($request->categories) && count($request->categories) > 0) {
                        $countryQuery->whereHas('categories', function($q) use ($request) {
                            $q->whereIn('site_categories.id', $request->categories);
                        });
                    }
                    
                    // Get IDs from both queries
                    $globalIds = $hasGlobalQuery->pluck('id')->toArray();
                    $countryIds = $countryQuery->pluck('id')->toArray();
                    
                    // Combine the IDs and create a new query to get the sites
                    $combinedIds = array_unique(array_merge($globalIds, $countryIds));
                    $query = Sites::with(['categories', 'countries', 'workPurposes', 'features'])
                        ->whereIn('id', $combinedIds);
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
            
            // Execute query
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

            // Return AJAX response with sites data
            return response()->json([
                'success' => true,
                'sites' => $sites,
                'count' => $sites->count()
            ]);
        } catch (\Exception $e) {
            // Log the exception
            \Log::error('Error in fetchSite: ' . $e->getMessage() . ' - ' . $e->getTraceAsString());
            
            // Return error response
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while fetching sites: ' . $e->getMessage(),
                'sites' => []
            ], 500);
        }
    }

    public function editSite($id)
    {
        try {
            \Log::info("Fetching site details for ID: {$id}");
            
            // First check if the site exists
            if (!Sites::where('id', $id)->exists()) {
                \Log::warning("Site with ID {$id} not found");
                return response()->json([
                    'status' => 404,
                    'message' => 'Site not found',
                ]);
            }
            
            // Fetch the site with relationships step by step to identify issues
            try {
                $site = Sites::findOrFail($id);
                \Log::info("Found site with ID {$id}: " . $site->url);
            } catch (\Exception $e) {
                \Log::error("Error fetching base site with ID {$id}: " . $e->getMessage());
                throw $e;
            }
            
            // Now load relationships one by one
            try {
                $site->load('categories');
                $siteCategories = $site->categories->pluck('id')->toArray();
                \Log::info("Loaded categories for site {$id}");
            } catch (\Exception $e) {
                \Log::error("Error loading categories for site {$id}: " . $e->getMessage());
                $siteCategories = [];
            }
            
            try {
                $site->load('countries');
                $isGlobal = $site->isGlobal();
                $siteCountries = $isGlobal ? [] : $site->countries->pluck('id')->toArray();
                \Log::info("Loaded countries for site {$id}, isGlobal: " . ($isGlobal ? 'true' : 'false'));
            } catch (\Exception $e) {
                \Log::error("Error loading countries for site {$id}: " . $e->getMessage());
                $isGlobal = false;
                $siteCountries = [];
            }
            
            try {
                $site->load('workPurposes');
                $sitePurposes = $site->workPurposes->pluck('id')->toArray();
                \Log::info("Loaded work purposes for site {$id}");
            } catch (\Exception $e) {
                \Log::error("Error loading work purposes for site {$id}: " . $e->getMessage());
                $sitePurposes = [];
            }
            
            try {
                $site->load('features');
                $siteFeatures = $site->features()->wherePivot('has_feature', true)->pluck('id')->toArray();
                \Log::info("Loaded features for site {$id}");
            } catch (\Exception $e) {
                \Log::error("Error loading features for site {$id}: " . $e->getMessage());
                $siteFeatures = [];
            }
            
            return response()->json([
                'status' => 200,
                'site' => $site,
                'site_categories' => $siteCategories,
                'is_global' => $isGlobal,
                'site_countries' => $siteCountries,
                'site_purposes' => $sitePurposes,
                'site_features' => $siteFeatures,
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            \Log::error('Site not found: ' . $e->getMessage());
            return response()->json([
                'status' => 404,
                'message' => 'Site not found',
            ]);
        } catch (\Exception $e) {
            \Log::error('Error in editSite: ' . $e->getMessage() . ' - ' . $e->getTraceAsString());
            return response()->json([
                'status' => 500,
                'message' => 'An error occurred while fetching site details: ' . $e->getMessage(),
            ]);
        }
    }

    public function updateSite(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'url'         => [
                'required',
                'string',
                'max:191',
                'regex:/^(([a-zA-Z0-9]|[a-zA-Z0-9][a-zA-Z0-9\-]*[a-zA-Z0-9])\.)+([A-Za-z0-9]|[A-Za-z0-9][A-Za-z0-9\-]*[A-Za-z0-9])$/', // Allow domains with subdomains
                function ($attribute, $value, $fail) use ($id) {
                    // Check if domain already exists (excluding current site)
                    $exists = Sites::where('url', $value)
                                  ->where('id', '!=', $id)
                                  ->exists();
                    if ($exists) {
                        $fail('This domain is already registered in the system.');
                    }
                },
            ],
            'da'          => 'nullable|integer|min:0|max:100',
            'description' => 'nullable|string',
            'video_link'  => 'nullable|string|url',
            'status'      => 'required|in:Live,Pending',
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
        
        // Check if the domain is reachable
        $domain = $request->input('url');
        $isReachable = $this->isDomainReachable($domain);
        $serverStatus = $isReachable ? 'Online' : 'Offline';
        
        // If site is not reachable and status was set to Live, change it to Pending
        $status = $request->input('status');
        if (!$isReachable && $status === 'Live') {
            $status = 'Pending';
        }

        // Update site using mass assignment
        $site->update([
            'name'         => $domain, // Use the URL as the name
            'url'          => $domain,
            'complete_url' => $request->input('complete_url'),
            'da'           => $request->input('da'),
            'description'  => $request->input('description'),
            'video_link'   => $request->input('video_link'),
            'status'       => $status,
            'server_status' => $serverStatus,
            'type'         => $request->input('type'),
            'theme'        => $request->input('theme'),
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
            // Make sure there are no duplicate purposes 
            $uniquePurposes = array_unique($request->purposes);
            $site->workPurposes()->sync($uniquePurposes);
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
        try {
            $validator = Validator::make($request->all(), [
                'categories' => 'required|array',
                'categories.*' => 'exists:site_categories,id',
                'option_type' => 'nullable|in:countries,purposes,features,all',
                'option_types' => 'nullable|array',
                'option_types.*' => 'in:countries,purposes,features',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->messages(),
                ], 422);
            }

            $categories = SiteCategory::whereIn('id', $request->categories)->get();
            $result = [];
            
            // If no categories found, return empty arrays
            if ($categories->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'countries' => [],
                    'purposes' => [],
                    'features' => [],
                    'message' => 'No categories found with the provided IDs'
                ]);
            }
            
            // Handle combined request with option_types array
            if ($request->has('option_types') && is_array($request->option_types)) {
                // Get all required data in a single operation for better performance
                $typesToGet = $request->option_types;
                
                // Include all options if 'all' is requested
                if (in_array('all', $typesToGet)) {
                    $typesToGet = ['countries', 'purposes', 'features'];
                }
                
                foreach ($typesToGet as $type) {
                    switch ($type) {
                        case 'countries':
                            $result['countries'] = $this->getCompatibleCountries($categories);
                            break;
                        case 'purposes':
                            $result['purposes'] = $this->getCompatiblePurposes($categories);
                            break;
                        case 'features':
                            $result['features'] = $this->getCompatibleFeatures($categories);
                            break;
                    }
                }
                
                return response()->json(array_merge(['success' => true], $result));
            }
            
            // Handle single option type request or 'all'
            $option_type = $request->input('option_type', 'all');
            
            if ($option_type === 'all') {
                return response()->json([
                    'success' => true,
                    'countries' => $this->getCompatibleCountries($categories),
                    'purposes' => $this->getCompatiblePurposes($categories),
                    'features' => $this->getCompatibleFeatures($categories),
                ]);
            }
            
            switch ($option_type) {
                case 'countries':
                    $compatibleOptions = $this->getCompatibleCountries($categories);
                    break;
                    
                case 'purposes':
                    $compatibleOptions = $this->getCompatiblePurposes($categories);
                    break;
                    
                case 'features':
                    $compatibleOptions = $this->getCompatibleFeatures($categories);
                    break;
                    
                default:
                    $compatibleOptions = [];
            }
            
            return response()->json([
                'success' => true,
                'compatible_options' => $compatibleOptions,
            ]);
        } catch (\Exception $e) {
            // Log the error
            \Log::error('Error in getCompatibleOptions: ' . $e->getMessage() . ' - ' . $e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage(),
            ], 500);
        }
    }
    
    /**
     * Get countries compatible with all selected categories
     */
    private function getCompatibleCountries($categories)
    {
        $query = Country::query();
        
        foreach ($categories as $category) {
            $query->whereHas('compatibleCategories', function ($q) use ($category) {
                $q->where('site_categories.id', $category->id);
            });
        }
        
        return $query->pluck('id')->toArray();
    }
    
    /**
     * Get work purposes compatible with all selected categories
     */
    private function getCompatiblePurposes($categories) 
    {
        $query = WorkPurpose::query();
        
        foreach ($categories as $category) {
            $query->whereHas('compatibleCategories', function ($q) use ($category) {
                $q->where('site_categories.id', $category->id);
            });
        }
        
        return $query->pluck('id')->toArray();
    }
    
    /**
     * Get features compatible with all selected categories
     */
    private function getCompatibleFeatures($categories)
    {
        $query = SiteFeature::query();
        
        foreach ($categories as $category) {
            $query->whereHas('compatibleCategories', function ($q) use ($category) {
                $q->where('site_categories.id', $category->id);
            });
        }
        
        return $query->pluck('id')->toArray();
    }

    /**
     * Check if a domain already exists
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkDomainExists(Request $request)
    {
        $domain = $request->input('domain');
        $excludeId = $request->input('exclude_id');
        
        $query = Sites::where('url', $domain);
        
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }
        
        $exists = $query->exists();
        
        return response()->json([
            'exists' => $exists
        ]);
    }

    /**
     * Check domain reachability from AJAX request
     */
    public function checkDomainReachability(Request $request)
    {
        $domain = $request->input('domain');
        
        if (empty($domain)) {
            return response()->json([
                'success' => false,
                'message' => 'Domain is required',
                'is_reachable' => false
            ]);
        }
        
        try {
            $isReachable = $this->isDomainReachable($domain);
            $method = '';
            
            if ($isReachable) {
                // Try to determine which method succeeded
                if ($this->checkUrl("https://" . $domain)) {
                    $method = 'HTTPS';
                } elseif ($this->checkUrl("http://" . $domain)) {
                    $method = 'HTTP';
                } elseif ($this->checkDnsRecord($domain)) {
                    $method = 'DNS';
                }
            }
            
            $message = $isReachable 
                ? 'Domain is reachable' . ($method ? " via $method" : '') 
                : 'Domain is not reachable. Please ensure the domain exists and is accessible.';
            
            return response()->json([
                'success' => true,
                'is_reachable' => $isReachable,
                'server_status' => $isReachable ? 'Online' : 'Offline',
                'method' => $method,
                'message' => $message
            ]);
        } catch (\Exception $e) {
            \Log::error("Error in checkDomainReachability: " . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'is_reachable' => false,
                'message' => 'Error checking domain: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Filter sites based on categories, countries, purposes, and minimum rating
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function filterSites(Request $request)
    {
        try {
            // Get filter parameters
            $categories = $request->input('categories', []);
            $isGlobal = (bool)$request->input('is_global', 0);
            $countries = $request->input('countries', []);
            $purposes = $request->input('purposes', []);
            $minRating = (float)$request->input('min_rating', 0);
            $sortByRating = (bool)$request->input('sort_by_rating', 1);
            
            // Get rating settings
            $ratingSettings = SiteSetting::getRatingSettings();
            $ratingScale = (float)$ratingSettings['rating_scale'];
            $ratingThresholdHigh = (float)$ratingSettings['rating_threshold_high'];
            $ratingThresholdMedium = (float)$ratingSettings['rating_threshold_medium'];
            $ratingDecimalPlaces = (int)$ratingSettings['rating_display_decimal_places'];
            
            // Start query
            $query = Sites::query()
                ->with(['categories', 'countries', 'workPurposes', 'features']);
            
            // Filter by categories if provided
            if (!empty($categories)) {
                $query->whereHas('categories', function($q) use ($categories) {
                    $q->whereIn('site_categories.id', $categories);
                }, '=', count($categories)); // Ensure all selected categories are present
            }
            
            // Filter by countries or global flag
            if ($isGlobal) {
                $query->where(function($q) {
                    $q->whereHas('countries', function($subQ) {
                        $subQ->where('site_country.is_global', 1);
                    })->orDoesntHave('countries'); // Include sites that have no countries specified
                });
            } else if (!empty($countries)) {
                $query->whereHas('countries', function($q) use ($countries) {
                    $q->whereIn('countries.id', $countries);
                });
            }
            
            // Filter by purposes if provided
            if (!empty($purposes)) {
                $query->whereHas('workPurposes', function($q) use ($purposes) {
                    $q->whereIn('work_purposes.id', $purposes);
                });
            }
            
            // Filter by minimum rating
            if ($minRating > 0) {
                $query->where(function($q) use ($minRating, $ratingScale) {
                    // Calculate and compare the rating
                    $siteIds = [];
                    $sites = Sites::with(['features'])->get();
                    
                    foreach ($sites as $site) {
                        $totalPoints = 0;
                        $maxPoints = 0;
                        
                        // Check if site has any features
                        if ($site->features->isNotEmpty()) {
                            $totalPoints = $site->features->sum('points');
                            $allFeatures = SiteFeature::all();
                            $maxPoints = $allFeatures->sum('points');
                        }
                        
                        // Calculate normalized rating using the dynamic scale
                        $rating = 0;
                        if ($maxPoints > 0) {
                            $rating = ($totalPoints / $maxPoints) * $ratingScale;
                        }
                        
                        // Compare with min rating
                        if ($rating >= $minRating) {
                            $siteIds[] = $site->id;
                        }
                    }
                    
                    $q->whereIn('id', $siteIds);
                });
            }
            
            // Apply ordering
            if ($sortByRating) {
                // Order sites by rating (need to calculate rating for each site)
                $sites = $query->get();
                
                // Map sites to include calculated rating
                $sitesWithRating = $sites->map(function($site) use ($ratingScale) {
                    $totalPoints = 0;
                    $maxPoints = 0;
                    
                    // Calculate rating based on features
                    if ($site->features->isNotEmpty()) {
                        $totalPoints = $site->features->sum('points');
                        $allFeatures = SiteFeature::all();
                        $maxPoints = $allFeatures->sum('points');
                    }
                    
                    // Calculate normalized rating using the dynamic scale
                    $rating = 0;
                    if ($maxPoints > 0) {
                        $rating = ($totalPoints / $maxPoints) * $ratingScale;
                    }
                    
                    // Add rating to site data
                    $site->rating = $rating;
                    $site->max_rating = $ratingScale;
                    return $site;
                });
                
                // Sort by rating in descending order
                $sortedSites = $sitesWithRating->sortByDesc('rating')->values();
            } else {
                // Order by created date if not sorting by rating
                $sites = $query->orderBy('created_at', 'desc')->get();
                
                // Calculate ratings for display
                $sortedSites = $sites->map(function($site) use ($ratingScale) {
                    $totalPoints = 0;
                    $maxPoints = 0;
                    
                    // Calculate rating based on features
                    if ($site->features->isNotEmpty()) {
                        $totalPoints = $site->features->sum('points');
                        $allFeatures = SiteFeature::all();
                        $maxPoints = $allFeatures->sum('points');
                    }
                    
                    // Calculate normalized rating using the dynamic scale
                    $rating = 0;
                    if ($maxPoints > 0) {
                        $rating = ($totalPoints / $maxPoints) * $ratingScale;
                    }
                    
                    // Add rating to site data
                    $site->rating = $rating;
                    $site->max_rating = $ratingScale;
                    return $site;
                });
            }
            
            // Transform sites to include simplified relationship data
            $formattedSites = $sortedSites->map(function($site) use ($ratingDecimalPlaces) {
                $siteData = $site->toArray();
                $siteData['categories_list'] = $site->categories->pluck('name')->join(', ');
                $siteData['is_global'] = $site->isGlobal();
                $siteData['countries_list'] = $site->countryList();
                $siteData['purposes_list'] = $site->workPurposes->pluck('name')->join(', ');
                
                // Format rating to specified decimal places
                $siteData['rating'] = round($site->rating, $ratingDecimalPlaces);
                
                return $siteData;
            });
            
            // Include rating settings in the response
            return response()->json([
                'success' => true,
                'sites' => $formattedSites,
                'rating_settings' => [
                    'scale' => $ratingScale,
                    'thresholdHigh' => $ratingThresholdHigh,
                    'thresholdMedium' => $ratingThresholdMedium,
                    'decimalPlaces' => $ratingDecimalPlaces
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('Error in filterSites: ' . $e->getMessage() . ' - ' . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class Sites extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'url',
        'complete_url',
        'description',
        'video_link',
        'status',
        'server_status',
        'type',
        'theme',
        'rating',
        'max_rating',
        'da',
    ];

    /**
     * Get the category associated with the site
     */
    public function category()
    {
        return $this->belongsTo(SiteCategory::class, 'category_id');
    }

    /**
     * Get the country associated with the site
     */
    public function country()
    {
        return $this->belongsTo(Country::class, 'country_id');
    }

    /**
     * Get the work purpose associated with the site
     */
    public function workPurpose()
    {
        return $this->belongsTo(WorkPurpose::class, 'purpose_id');
    }

    /**
     * Get the categories associated with this site (many-to-many)
     */
    public function categories()
    {
        try {
            return $this->belongsToMany(SiteCategory::class, 'site_category_relations', 'site_id', 'category_id')
                        ->withTimestamps();
        } catch (\Exception $e) {
            Log::error('Error in Sites->categories relation: ' . $e->getMessage());
            return $this->belongsToMany(SiteCategory::class, 'site_category_relations', 'site_id', 'category_id');
        }
    }

    /**
     * Get the countries associated with this site (many-to-many)
     */
    public function countries()
    {
        try {
            return $this->belongsToMany(Country::class, 'site_country_relations', 'site_id', 'country_id')
                        ->withPivot('is_global')
                        ->withTimestamps();
        } catch (\Exception $e) {
            Log::error('Error in Sites->countries relation: ' . $e->getMessage());
            return $this->belongsToMany(Country::class, 'site_country_relations', 'site_id', 'country_id');
        }
    }

    /**
     * Get the work purposes associated with this site (many-to-many)
     */
    public function workPurposes()
    {
        try {
            return $this->belongsToMany(WorkPurpose::class, 'site_work_purpose_relations', 'site_id', 'purpose_id')
                        ->withTimestamps();
        } catch (\Exception $e) {
            Log::error('Error in Sites->workPurposes relation: ' . $e->getMessage());
            return $this->belongsToMany(WorkPurpose::class, 'site_work_purpose_relations', 'site_id', 'purpose_id');
        }
    }

    /**
     * Get the features associated with this site
     */
    public function features()
    {
        try {
            return $this->belongsToMany(SiteFeature::class, 'site_feature_relations', 'site_id', 'site_feature_id')
                        ->withPivot('has_feature')
                        ->withTimestamps();
        } catch (\Exception $e) {
            Log::error('Error in Sites->features relation: ' . $e->getMessage());
            return $this->belongsToMany(SiteFeature::class, 'site_feature_relations', 'site_id', 'site_feature_id');
        }
    }

    /**
     * Check if a site is global
     */
    public function isGlobal()
    {
        try {
            return $this->countries()->wherePivot('is_global', true)->exists();
        } catch (\Exception $e) {
            Log::error('Error in Sites->isGlobal method: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get a formatted list of countries
     */
    public function countryList()
    {
        try {
            if ($this->isGlobal()) {
                return 'Global';
            }
            
            return $this->countries->pluck('name')->join(', ');
        } catch (\Exception $e) {
            Log::error('Error in Sites->countryList method: ' . $e->getMessage());
            return 'Unknown';
        }
    }

    /**
     * Calculate and update the site's rating based on its features
     */
    public function calculateRating()
    {
        try {
            // Get all features
            $allFeatures = SiteFeature::all();
            $maxRating = $allFeatures->sum('points');
            
            // Get the site's features
            $siteFeatures = $this->features()->wherePivot('has_feature', true)->get();
            $siteRating = $siteFeatures->sum('points');
            
            // Update the site's rating
            $this->rating = $maxRating > 0 ? $siteRating : 0;
            $this->save();
            
            return $this->rating;
        } catch (\Exception $e) {
            Log::error('Error in Sites->calculateRating method: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Set this site as global
     */
    public function setGlobal($isGlobal = true)
    {
        // If setting to global, first clear all country relationships
        if ($isGlobal) {
            $this->countries()->detach();
            
            // Add a global entry with the first country (arbitrary)
            $country = Country::first();
            if ($country) {
                $this->countries()->attach($country->id, ['is_global' => true]);
            }
        }
        
        return $this;
    }
}

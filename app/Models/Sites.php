<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sites extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'url',
        'description',
        'status',
        'type',
        'theme',
        'rating',
        'max_rating',
    ];

    /**
     * Get the categories associated with this site
     */
    public function categories()
    {
        return $this->belongsToMany(SiteCategory::class, 'site_category_relations', 'site_id', 'category_id')
                    ->withTimestamps();
    }

    /**
     * Get the countries associated with this site
     */
    public function countries()
    {
        return $this->belongsToMany(Country::class, 'site_country_relations', 'site_id', 'country_id')
                    ->withPivot('is_global')
                    ->withTimestamps();
    }

    /**
     * Get the work purposes associated with this site
     */
    public function workPurposes()
    {
        return $this->belongsToMany(WorkPurpose::class, 'site_work_purpose_relations', 'site_id', 'purpose_id')
                    ->withTimestamps();
    }

    /**
     * Get the features for this site
     */
    public function features()
    {
        return $this->belongsToMany(SiteFeature::class, 'site_feature_relations', 'site_id', 'site_feature_id')
                    ->withPivot('has_feature')
                    ->withTimestamps();
    }

    /**
     * Calculate the site's rating based on features
     */
    public function calculateRating()
    {
        $siteFeatures = $this->features()->wherePivot('has_feature', true)->get();
        $totalPoints = $siteFeatures->sum('points');
        $maxRating = SiteFeature::sum('points');
        
        if ($maxRating > 0) {
            $this->rating = ($totalPoints / $maxRating) * 10; // Scale to 10
            $this->max_rating = 10;
            $this->save();
        }
        
        return $this->rating;
    }

    /**
     * Check if this site is global (available in all countries)
     */
    public function isGlobal()
    {
        return $this->countries()->wherePivot('is_global', true)->exists();
    }

    /**
     * Get the list of countries with information about global status
     */
    public function countryList()
    {
        $countries = $this->countries()->get();
        $isGlobal = $this->isGlobal();
        
        if ($isGlobal) {
            return 'Global' . ($countries->count() > 0 ? ' + ' . $countries->pluck('name')->join(', ') : '');
        }
        
        return $countries->pluck('name')->join(', ');
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

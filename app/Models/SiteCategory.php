<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SiteCategory extends Model
{
    use HasFactory;

    protected $table = 'site_categories';

    protected $fillable = [
        'name',
        'description',
    ];

    /**
     * Get the sites associated with this category
     */
    public function sites()
    {
        return $this->hasMany(Sites::class, 'category_id');
    }
    
    /**
     * Get the sites through the category-site relation
     */
    public function sitesThroughRelation()
    {
        return $this->belongsToMany(Sites::class, 'site_category_relations', 'category_id', 'site_id');
    }
    
    /**
     * Get compatible countries for this category
     */
    public function compatibleCountries()
    {
        return $this->belongsToMany(Country::class, 'category_country_compatibility', 'category_id', 'country_id')
                    ->withTimestamps();
    }
    
    /**
     * Get compatible work purposes for this category
     */
    public function compatibleWorkPurposes()
    {
        return $this->belongsToMany(WorkPurpose::class, 'category_purpose_compatibility', 'category_id', 'purpose_id')
                    ->withTimestamps();
    }
    
    /**
     * Get compatible features for this category
     */
    public function compatibleFeatures()
    {
        return $this->belongsToMany(SiteFeature::class, 'category_feature_compatibility', 'category_id', 'feature_id')
                    ->withTimestamps();
    }
}
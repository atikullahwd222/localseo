<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SiteFeature extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'points',
    ];

    /**
     * Get the sites that have this feature
     */
    public function sites()
    {
        return $this->belongsToMany(Sites::class, 'site_feature_relations', 'site_feature_id', 'site_id')
                    ->withPivot('has_feature')
                    ->withTimestamps();
    }

    /**
     * Get the categories this feature is compatible with
     */
    public function compatibleCategories()
    {
        return $this->belongsToMany(SiteCategory::class, 'category_feature_compatibility', 'feature_id', 'category_id')
                    ->withTimestamps();
    }
} 
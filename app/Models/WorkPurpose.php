<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkPurpose extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
    ];

    /**
     * Get the sites associated with this work purpose
     */
    public function sites()
    {
        return $this->hasMany(Sites::class, 'purpose_id');
    }
    
    /**
     * Get the sites through the many-to-many relation
     */
    public function sitesManyToMany()
    {
        return $this->belongsToMany(Sites::class, 'site_work_purpose_relations', 'purpose_id', 'site_id')
                    ->withTimestamps();
    }
    
    /**
     * Get the categories this purpose is compatible with
     */
    public function compatibleCategories()
    {
        return $this->belongsToMany(SiteCategory::class, 'category_purpose_compatibility', 'purpose_id', 'category_id')
                    ->withTimestamps();
    }
} 
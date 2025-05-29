<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
    ];

    /**
     * Get the sites associated with this country
     */
    public function sites()
    {
        return $this->hasMany(Sites::class, 'country_id');
    }

    /**
     * Get the categories this country is compatible with
     */
    public function compatibleCategories()
    {
        return $this->belongsToMany(SiteCategory::class, 'category_country_compatibility', 'country_id', 'category_id')
                    ->withTimestamps();
    }
} 
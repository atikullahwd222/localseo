<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SiteCategory;
use App\Models\Country;
use App\Models\WorkPurpose;
use App\Models\SiteFeature;

class CategoryCompatibilitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all categories, countries, purposes, and features
        $categories = SiteCategory::all();
        $countries = Country::all();
        $purposes = WorkPurpose::all();
        $features = SiteFeature::all();
        
        // If any of these are empty, we can't set up compatibility
        if ($categories->isEmpty() || $countries->isEmpty() || $purposes->isEmpty() || $features->isEmpty()) {
            return;
        }
        
        // For each category, assign compatible countries, purposes, and features
        // In a real system, this would be more selective based on actual compatibility rules
        foreach ($categories as $category) {
            // Assign all countries as compatible (you would be more selective in a real system)
            foreach ($countries as $country) {
                $category->compatibleCountries()->syncWithoutDetaching([$country->id]);
            }
            
            // Assign all work purposes as compatible (you would be more selective in a real system)
            foreach ($purposes as $purpose) {
                $category->compatibleWorkPurposes()->syncWithoutDetaching([$purpose->id]);
            }
            
            // Assign all features as compatible (you would be more selective in a real system)
            foreach ($features as $feature) {
                $category->compatibleFeatures()->syncWithoutDetaching([$feature->id]);
            }
        }
    }
} 
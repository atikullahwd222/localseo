<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Create site_category_relations table if it doesn't exist
        if (!Schema::hasTable('site_category_relations')) {
            Schema::create('site_category_relations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('site_id')->constrained('sites')->onDelete('cascade');
                $table->foreignId('category_id')->constrained('site_categories')->onDelete('cascade');
                $table->timestamps();
                
                // Ensure each site-category combination is unique
                $table->unique(['site_id', 'category_id']);
            });
        }
        
        // Create site_country_relations table if it doesn't exist
        if (!Schema::hasTable('site_country_relations')) {
            Schema::create('site_country_relations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('site_id')->constrained('sites')->onDelete('cascade');
                $table->foreignId('country_id')->constrained('countries')->onDelete('cascade');
                $table->boolean('is_global')->default(false)->comment('If true, applies to all countries');
                $table->timestamps();
                
                // Ensure each site-country combination is unique
                $table->unique(['site_id', 'country_id']);
            });
        }
        
        // Create site_work_purpose_relations table if it doesn't exist
        if (!Schema::hasTable('site_work_purpose_relations')) {
            Schema::create('site_work_purpose_relations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('site_id')->constrained('sites')->onDelete('cascade');
                $table->foreignId('purpose_id')->constrained('work_purposes')->onDelete('cascade');
                $table->timestamps();
                
                // Ensure each site-purpose combination is unique
                $table->unique(['site_id', 'purpose_id']);
            });
        }
        
        // Create site_feature_relations table if it doesn't exist
        if (!Schema::hasTable('site_feature_relations')) {
            Schema::create('site_feature_relations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('site_id')->constrained('sites')->onDelete('cascade');
                $table->foreignId('site_feature_id')->constrained('site_features')->onDelete('cascade');
                $table->boolean('has_feature')->default(false);
                $table->timestamps();
                
                // Ensure each site-feature combination is unique
                $table->unique(['site_id', 'site_feature_id']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Don't drop tables in down method, as they might contain important data
    }
};

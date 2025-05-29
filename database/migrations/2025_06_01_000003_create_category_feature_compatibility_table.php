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
        Schema::create('category_feature_compatibility', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('site_categories')->onDelete('cascade');
            $table->foreignId('feature_id')->constrained('site_features')->onDelete('cascade');
            $table->timestamps();
            
            // Ensure each category-feature combination is unique
            $table->unique(['category_id', 'feature_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('category_feature_compatibility');
    }
}; 
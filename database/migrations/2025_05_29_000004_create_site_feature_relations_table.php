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

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('site_feature_relations');
    }
}; 
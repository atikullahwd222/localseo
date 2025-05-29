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
        Schema::create('category_purpose_compatibility', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('site_categories')->onDelete('cascade');
            $table->foreignId('purpose_id')->constrained('work_purposes')->onDelete('cascade');
            $table->timestamps();
            
            // Ensure each category-purpose combination is unique
            $table->unique(['category_id', 'purpose_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('category_purpose_compatibility');
    }
}; 
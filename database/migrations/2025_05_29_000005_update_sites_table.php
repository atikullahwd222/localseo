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
        Schema::table('sites', function (Blueprint $table) {
            // Add new columns with foreign keys
            $table->foreignId('category_id')->nullable()->after('theme')->constrained('site_categories')->nullOnDelete();
            $table->foreignId('country_id')->nullable()->after('category_id')->constrained('countries')->nullOnDelete();
            $table->foreignId('purpose_id')->nullable()->after('country_id')->constrained('work_purposes')->nullOnDelete();
            $table->decimal('rating', 5, 2)->default(0)->after('purpose_id')->comment('Site rating');
            $table->integer('max_rating')->default(10)->after('rating')->comment('Maximum possible rating');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sites', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropForeign(['country_id']);
            $table->dropForeign(['purpose_id']);
            $table->dropColumn(['category_id', 'country_id', 'purpose_id', 'rating', 'max_rating']);
        });
    }
}; 
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('sites', function (Blueprint $table) {
            $table->string('complete_url')->nullable()->after('url')->comment('Complete URL with http/https');
        });
        
        // Update existing records to set complete_url based on url in a separate statement
        DB::statement("UPDATE sites SET complete_url = CONCAT('https://', url)");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sites', function (Blueprint $table) {
            $table->dropColumn('complete_url');
        });
    }
};

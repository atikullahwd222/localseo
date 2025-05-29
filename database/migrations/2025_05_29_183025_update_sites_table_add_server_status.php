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
            // Add server_status field
            $table->string('server_status')->default('Online')->after('status');
            
            // Update existing status values from active/inactive to Live/Pending
            DB::statement("UPDATE sites SET status = CASE WHEN status = 'active' THEN 'Live' WHEN status = 'inactive' THEN 'Pending' ELSE status END");
            
            // Set default value for status to Live
            DB::statement("ALTER TABLE sites MODIFY COLUMN status VARCHAR(255) DEFAULT 'Live'");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sites', function (Blueprint $table) {
            // Remove server_status field
            $table->dropColumn('server_status');
            
            // Revert status values back to active/inactive
            DB::statement("UPDATE sites SET status = CASE WHEN status = 'Live' THEN 'active' WHEN status = 'Pending' THEN 'inactive' ELSE status END");
            
            // Set default value for status back to active
            DB::statement("ALTER TABLE sites MODIFY COLUMN status VARCHAR(255) DEFAULT 'active'");
        });
    }
};

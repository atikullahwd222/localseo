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
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('role_id')->nullable()->constrained('roles')->onDelete('set null');
            
            // Only add the status column if it doesn't already exist
            if (!Schema::hasColumn('users', 'status')) {
                $table->enum('status', ['active', 'inactive', 'suspended'])->default('inactive');
            }
        });
        
        // Set the default role for existing users to 'user' (ID 3)
        DB::statement('UPDATE users SET role_id = 3, status = "active" WHERE role_id IS NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['role_id']);
            $table->dropColumn(['role_id']);
            
            // Only drop status if we created it
            if (Schema::hasColumn('users', 'status')) {
                $table->dropColumn(['status']);
            }
        });
    }
};

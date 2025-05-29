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
        // First ensure all users have a valid role_id based on their role string
        if (Schema::hasColumn('users', 'role') && Schema::hasColumn('users', 'role_id')) {
            // Get all roles for mapping
            $roles = DB::table('roles')->get();
            $roleMap = [];
            foreach ($roles as $role) {
                $roleMap[$role->name] = $role->id;
            }
            
            // Get default user role id for fallback
            $defaultRoleId = $roleMap['user'] ?? null;
            
            // Update all users without a role_id but with a role string
            $users = DB::table('users')->whereNull('role_id')->whereNotNull('role')->get();
            foreach ($users as $user) {
                $roleName = $user->role;
                $roleId = $roleMap[$roleName] ?? $defaultRoleId;
                
                if ($roleId) {
                    DB::table('users')->where('id', $user->id)->update(['role_id' => $roleId]);
                }
            }
            
            // Ensure all users have a role_id (use default for any remaining)
            if ($defaultRoleId) {
                DB::table('users')->whereNull('role_id')->update(['role_id' => $defaultRoleId]);
            }
        }
        
        // Now drop the role column
        if (Schema::hasColumn('users', 'role')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('role');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Add back role column if needed for rollback
        if (!Schema::hasColumn('users', 'role')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('role')->nullable()->after('role_id');
            });
            
            // Populate role strings based on role_id
            $roles = DB::table('roles')->get();
            $roleMap = [];
            foreach ($roles as $role) {
                $roleMap[$role->id] = $role->name;
            }
            
            $users = DB::table('users')->whereNotNull('role_id')->get();
            foreach ($users as $user) {
                $roleName = $roleMap[$user->role_id] ?? 'user';
                DB::table('users')->where('id', $user->id)->update(['role' => $roleName]);
            }
        }
    }
};

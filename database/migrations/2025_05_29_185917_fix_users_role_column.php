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
        // First create the roles table if it doesn't exist
        if (!Schema::hasTable('roles')) {
            Schema::create('roles', function (Blueprint $table) {
                $table->id();
                $table->string('name')->unique();
                $table->string('description')->nullable();
                $table->timestamps();
            });
            
            // Insert default roles
            DB::table('roles')->insert([
                ['name' => 'admin', 'description' => 'Administrator with full access', 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'editor', 'description' => 'Editor with site management access', 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'user', 'description' => 'Regular user with read-only access', 'created_at' => now(), 'updated_at' => now()]
            ]);
        }
        
        Schema::table('users', function (Blueprint $table) {
            // Add role_id column if it doesn't exist
            if (!Schema::hasColumn('users', 'role_id')) {
                $table->foreignId('role_id')->nullable()->after('phone')->constrained('roles')->onDelete('set null');
            }
        });
        
        // If both role and role_id columns exist, update role_id based on role string
        if (Schema::hasColumn('users', 'role') && Schema::hasColumn('users', 'role_id')) {
            $roles = DB::table('roles')->get();
            $roleMap = [];
            foreach ($roles as $role) {
                $roleMap[$role->name] = $role->id;
            }
            
            $users = DB::table('users')->whereNull('role_id')->whereNotNull('role')->get();
            foreach ($users as $user) {
                $roleName = $user->role;
                $roleId = $roleMap[$roleName] ?? $roleMap['user'] ?? null;
                
                if ($roleId) {
                    DB::table('users')->where('id', $user->id)->update(['role_id' => $roleId]);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No need to reverse anything as we're just fixing existing columns
    }
};

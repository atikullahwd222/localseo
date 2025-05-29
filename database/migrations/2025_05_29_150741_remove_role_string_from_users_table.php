<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\User;
use App\Models\Role;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First, make sure all users have role_id set based on their existing string role
        $users = User::whereNull('role_id')->whereNotNull('role')->get();
        $roles = Role::all()->keyBy('name');
        
        foreach ($users as $user) {
            $roleName = $user->role;
            if ($roles->has($roleName)) {
                $user->role_id = $roles[$roleName]->id;
                $user->save();
            } else {
                // Default to user role if the string role doesn't exist
                $userRole = $roles->get('user');
                if ($userRole) {
                    $user->role_id = $userRole->id;
                    $user->save();
                }
            }
        }
        
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Only add the role column if it doesn't exist
        if (!Schema::hasColumn('users', 'role')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('role')->default('user')->nullable()->after('role_id');
            });
            
            // Restore role strings based on role_id relationships
            $users = User::with('role')->get();
            
            foreach ($users as $user) {
                if ($user->role) {
                    $user->role = $user->role->name;
                    $user->save();
                }
            }
        }
    }
};

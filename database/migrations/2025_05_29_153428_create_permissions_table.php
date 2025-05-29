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
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('module')->nullable();
            $table->timestamps();
        });
        
        // Insert default permissions
        $this->seedDefaultPermissions();
    }
    
    /**
     * Seed default permissions
     */
    private function seedDefaultPermissions()
    {
        $now = now();
        $permissions = [
            // User Management
            [
                'name' => 'View Users',
                'slug' => 'users.view',
                'description' => 'Can view user list',
                'module' => 'users',
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'name' => 'Create Users',
                'slug' => 'users.create',
                'description' => 'Can create new users',
                'module' => 'users',
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'name' => 'Edit Users',
                'slug' => 'users.edit',
                'description' => 'Can edit existing users',
                'module' => 'users',
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'name' => 'Delete Users',
                'slug' => 'users.delete',
                'description' => 'Can delete users',
                'module' => 'users',
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'name' => 'Approve Users',
                'slug' => 'users.approve',
                'description' => 'Can approve pending users',
                'module' => 'users',
                'created_at' => $now,
                'updated_at' => $now
            ],
            
            // Role Management
            [
                'name' => 'View Roles',
                'slug' => 'roles.view',
                'description' => 'Can view roles',
                'module' => 'roles',
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'name' => 'Create Roles',
                'slug' => 'roles.create',
                'description' => 'Can create new roles',
                'module' => 'roles',
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'name' => 'Edit Roles',
                'slug' => 'roles.edit',
                'description' => 'Can edit existing roles',
                'module' => 'roles',
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'name' => 'Delete Roles',
                'slug' => 'roles.delete',
                'description' => 'Can delete roles',
                'module' => 'roles',
                'created_at' => $now,
                'updated_at' => $now
            ],
            
            // Permission Management
            [
                'name' => 'View Permissions',
                'slug' => 'permissions.view',
                'description' => 'Can view permissions',
                'module' => 'permissions',
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'name' => 'Manage Permissions',
                'slug' => 'permissions.manage',
                'description' => 'Can assign/revoke permissions',
                'module' => 'permissions',
                'created_at' => $now,
                'updated_at' => $now
            ],
            
            // Site Management
            [
                'name' => 'View Sites',
                'slug' => 'sites.view',
                'description' => 'Can view sites',
                'module' => 'sites',
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'name' => 'Create Sites',
                'slug' => 'sites.create',
                'description' => 'Can create new sites',
                'module' => 'sites',
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'name' => 'Edit Sites',
                'slug' => 'sites.edit',
                'description' => 'Can edit existing sites',
                'module' => 'sites',
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'name' => 'Delete Sites',
                'slug' => 'sites.delete',
                'description' => 'Can delete sites',
                'module' => 'sites',
                'created_at' => $now,
                'updated_at' => $now
            ],
            
            // Settings
            [
                'name' => 'Manage Settings',
                'slug' => 'settings.manage',
                'description' => 'Can manage application settings',
                'module' => 'settings',
                'created_at' => $now,
                'updated_at' => $now
            ],
            
            // Session Management
            [
                'name' => 'View Sessions',
                'slug' => 'sessions.view',
                'description' => 'Can view active sessions',
                'module' => 'sessions',
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'name' => 'Terminate Sessions',
                'slug' => 'sessions.terminate',
                'description' => 'Can terminate user sessions',
                'module' => 'sessions',
                'created_at' => $now,
                'updated_at' => $now
            ]
        ];
        
        DB::table('permissions')->insert($permissions);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('permissions');
    }
};

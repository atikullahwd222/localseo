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
        // Get the admin role ID
        $adminRole = DB::table('roles')->where('name', 'admin')->first();
        
        if ($adminRole) {
            // First, clear any existing permissions for the admin role
            DB::table('role_permission')->where('role_id', $adminRole->id)->delete();
            
            // Get all permissions
            $permissions = DB::table('permissions')->pluck('id')->toArray();
            
            // Prepare data for insertion
            $data = [];
            $now = now();
            
            foreach ($permissions as $permissionId) {
                $data[] = [
                    'role_id' => $adminRole->id,
                    'permission_id' => $permissionId,
                    'created_at' => $now,
                    'updated_at' => $now
                ];
            }
            
            // Insert all permissions for admin role
            if (!empty($data)) {
                DB::table('role_permission')->insert($data);
            }
        }
        
        // Also remove all empty assignment migration files that aren't needed
        $this->removeUnwantedMigrationFiles();
    }
    
    /**
     * Remove unwanted migration files that are just placeholders
     */
    private function removeUnwantedMigrationFiles()
    {
        $filesToDelete = [
            '2025_05_29_155551_assign_all_permissions_to_admin.php',
            '2025_05_29_155909_assign_permissions_to_default_roles.php',
            '2025_05_29_160036_assign_permissions_to_default_roles.php',
            '2025_05_29_160049_assign_permissions_to_default_roles.php'
        ];
        
        $migrationPath = database_path('migrations');
        
        foreach ($filesToDelete as $file) {
            $fullPath = $migrationPath . DIRECTORY_SEPARATOR . $file;
            if (file_exists($fullPath)) {
                @unlink($fullPath);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Get the admin role ID
        $adminRole = DB::table('roles')->where('name', 'admin')->first();
        
        if ($adminRole) {
            // Remove all permissions for the admin role
            DB::table('role_permission')->where('role_id', $adminRole->id)->delete();
        }
    }
};

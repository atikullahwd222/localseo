<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Role;
use App\Models\Permission;

class SetupAdminPermissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:setup-permissions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set up all permissions for the admin role';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Setting up permissions for admin role...');

        // Get admin role
        $adminRole = Role::where('name', 'admin')->first();
        
        if (!$adminRole) {
            $this->error('Admin role not found! Creating it...');
            $adminRole = Role::create([
                'name' => 'admin',
                'description' => 'Administrator with full access'
            ]);
        }

        // Get all permissions
        $permissions = Permission::all();
        
        if ($permissions->isEmpty()) {
            $this->error('No permissions found in the database!');
            return 1;
        }
        
        // Clear existing permissions for admin
        DB::table('role_permission')->where('role_id', $adminRole->id)->delete();
        
        // Assign all permissions to admin
        $now = now();
        $data = [];
        
        foreach ($permissions as $permission) {
            $data[] = [
                'role_id' => $adminRole->id,
                'permission_id' => $permission->id,
                'created_at' => $now,
                'updated_at' => $now
            ];
        }
        
        DB::table('role_permission')->insert($data);
        
        $this->info('Successfully assigned ' . count($data) . ' permissions to admin role!');
        
        return 0;
    }
}

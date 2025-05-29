<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Role;
use App\Models\Permission;

class SyncRolePermissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'roles:sync-permissions {--force : Force sync even if roles already have permissions}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronize default permissions for all roles';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Syncing role permissions...');
        
        // Get roles
        $adminRole = Role::where('name', 'admin')->first();
        $editorRole = Role::where('name', 'editor')->first();
        $userRole = Role::where('name', 'user')->first();
        
        if (!$adminRole || !$editorRole || !$userRole) {
            $this->error('Default roles are missing. Please run migrations first.');
            return 1;
        }
        
        // Check if roles already have permissions
        if (!$this->option('force')) {
            if ($adminRole->permissions()->count() > 0) {
                $this->warn('Admin role already has permissions. Use --force to override.');
            }
            
            if ($editorRole->permissions()->count() > 0) {
                $this->warn('Editor role already has permissions. Use --force to override.');
            }
            
            if ($userRole->permissions()->count() > 0) {
                $this->warn('User role already has permissions. Use --force to override.');
            }
            
            if (!$this->confirm('Continue anyway?')) {
                return 0;
            }
        }
        
        // Get all permissions
        $allPermissions = Permission::all();
        
        if ($allPermissions->isEmpty()) {
            $this->error('No permissions found. Please run migrations first.');
            return 1;
        }
        
        // Assign all permissions to admin
        $adminRole->permissions()->sync($allPermissions->pluck('id')->toArray());
        $this->info('Admin role: ' . $allPermissions->count() . ' permissions assigned.');
        
        // Assign specific permissions to editor
        $editorPermissions = Permission::where('module', 'sites')
            ->orWhere('slug', 'users.approve')
            ->orWhere('slug', 'users.view')
            ->orWhere('slug', 'sessions.view')
            ->get();
            
        $editorRole->permissions()->sync($editorPermissions->pluck('id')->toArray());
        $this->info('Editor role: ' . $editorPermissions->count() . ' permissions assigned.');
        
        // Assign basic permissions to user
        $userPermissions = Permission::where('slug', 'sites.view')
            ->get();
            
        $userRole->permissions()->sync($userPermissions->pluck('id')->toArray());
        $this->info('User role: ' . $userPermissions->count() . ' permissions assigned.');
        
        $this->info('Role permissions synchronized successfully!');
        
        return 0;
    }
}

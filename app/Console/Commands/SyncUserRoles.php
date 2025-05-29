<?php

namespace App\Console\Commands;

use App\Models\Role;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SyncUserRoles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:sync-roles {--force : Force sync even for users with existing role_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronize legacy string-based roles with the new role_id foreign key';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting role synchronization...');
        
        // Get all roles 
        $roles = Role::all()->keyBy('name');
        
        if ($roles->isEmpty()) {
            $this->error('No roles found in the database. Please run migrations first.');
            return 1;
        }
        
        // Get users that need synchronization
        $query = User::query();
        
        // Only sync users with null role_id unless --force is used
        if (!$this->option('force')) {
            $query->whereNull('role_id');
        }
        
        $users = $query->get();
        
        $this->info("Found {$users->count()} users to synchronize.");
        
        $updated = 0;
        $skipped = 0;
        $errors = 0;
        
        foreach ($users as $user) {
            try {
                $roleString = $user->role;
                
                // Skip if no string role
                if (empty($roleString)) {
                    $this->warn("User ID {$user->id} has no role string defined. Skipping.");
                    $skipped++;
                    continue;
                }
                
                // Find matching role
                $role = $roles->get($roleString);
                
                if (!$role) {
                    $this->warn("User ID {$user->id} has unknown role '{$roleString}'. Setting to default 'user' role.");
                    $role = $roles->get('user');
                    
                    if (!$role) {
                        $this->error("Default 'user' role not found. Skipping.");
                        $skipped++;
                        continue;
                    }
                }
                
                // Update user's role_id
                $user->role_id = $role->id;
                $user->save();
                
                $updated++;
                $this->line("Updated user ID {$user->id} ({$user->email}): {$roleString} → role_id: {$role->id}");
                
            } catch (\Exception $e) {
                $this->error("Error updating user ID {$user->id}: {$e->getMessage()}");
                $errors++;
            }
        }
        
        $this->newLine();
        $this->info("Role synchronization complete:");
        $this->info("- Updated: {$updated}");
        $this->info("- Skipped: {$skipped}");
        $this->info("- Errors: {$errors}");
        
        return 0;
    }
}

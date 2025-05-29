<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Role;
use App\Models\Permission;

class FixRolePermissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:role-permissions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix any invalid role-permission associations';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking for invalid role-permission associations...');

        // Get all role_permission entries
        $rolePermissions = DB::table('role_permission')->get();
        
        $invalidCount = 0;
        $fixedCount = 0;
        
        foreach ($rolePermissions as $rp) {
            // Check if role exists
            $roleExists = DB::table('roles')->where('id', $rp->role_id)->exists();
            
            // Check if permission exists
            $permissionExists = DB::table('permissions')->where('id', $rp->permission_id)->exists();
            
            if (!$roleExists || !$permissionExists) {
                $this->error("Invalid association found: role_id={$rp->role_id}, permission_id={$rp->permission_id}");
                
                // Delete the invalid association
                DB::table('role_permission')
                    ->where('id', $rp->id)
                    ->delete();
                    
                $invalidCount++;
                $fixedCount++;
            }
        }
        
        // Check for duplicate entries
        $duplicates = DB::table('role_permission')
            ->select('role_id', 'permission_id', DB::raw('COUNT(*) as count'))
            ->groupBy('role_id', 'permission_id')
            ->having('count', '>', 1)
            ->get();
            
        foreach ($duplicates as $duplicate) {
            $this->error("Duplicate association found: role_id={$duplicate->role_id}, permission_id={$duplicate->permission_id}");
            
            // Keep one entry and delete the rest
            $entries = DB::table('role_permission')
                ->where('role_id', $duplicate->role_id)
                ->where('permission_id', $duplicate->permission_id)
                ->orderBy('id')
                ->get();
                
            // Skip the first one and delete the rest
            for ($i = 1; $i < count($entries); $i++) {
                DB::table('role_permission')
                    ->where('id', $entries[$i]->id)
                    ->delete();
                    
                $fixedCount++;
            }
        }
        
        if ($invalidCount === 0 && count($duplicates) === 0) {
            $this->info('No invalid role-permission associations found.');
        } else {
            $this->info("Fixed {$fixedCount} invalid role-permission associations.");
        }
        
        // Run the admin:setup-permissions command to ensure admin has all permissions
        $this->call('admin:setup-permissions');
        
        return 0;
    }
} 
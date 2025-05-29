<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description'
    ];

    /**
     * Get the users with this role
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }
    
    /**
     * Get the permissions associated with this role
     */
    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'role_permission');
    }
    
    /**
     * Check if the role has a specific permission
     */
    public function hasPermission($permission)
    {
        if (is_string($permission)) {
            return $this->permissions->contains('slug', $permission);
        }
        
        return $permission->intersect($this->permissions)->count() > 0;
    }
    
    /**
     * Assign permission(s) to a role
     */
    public function givePermissionTo(...$permissions)
    {
        try {
            $permissions = collect($permissions)
                ->flatten()
                ->map(function ($permission) {
                    if (is_string($permission)) {
                        $perm = Permission::where('slug', $permission)->first();
                        return $perm ? $perm->id : false;
                    }
                    
                    return $permission->id ?? false;
                })
                ->filter(function ($permission) {
                    return $permission;
                })->toArray();
            
            if (empty($permissions)) {
                throw new \Exception("No valid permissions found to assign");
            }
            
            $this->permissions()->syncWithoutDetaching($permissions);
            
            return $this;
        } catch (\Exception $e) {
            \Log::error('Error assigning permission to role: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Remove permission(s) from a role
     */
    public function removePermission(...$permissions)
    {
        try {
            $permissions = collect($permissions)
                ->flatten()
                ->map(function ($permission) {
                    if (is_string($permission)) {
                        $perm = Permission::where('slug', $permission)->first();
                        return $perm ? $perm->id : false;
                    }
                    
                    return $permission->id ?? false;
                })
                ->filter(function ($permission) {
                    return $permission;
                })->toArray();
                
            if (empty($permissions)) {
                throw new \Exception("No valid permissions found to remove");
            }
            
            $this->permissions()->detach($permissions);
            
            return $this;
        } catch (\Exception $e) {
            \Log::error('Error removing permission from role: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Sync role permissions
     */
    public function syncPermissions($permissions)
    {
        $this->permissions()->sync([]);
        
        return $this->givePermissionTo($permissions);
    }
    
    /**
     * Check if the role is admin
     */
    public function isAdmin()
    {
        return $this->name === 'admin';
    }
    
    /**
     * Check if the role is editor
     */
    public function isEditor()
    {
        return $this->name === 'editor';
    }
    
    /**
     * Check if the role is user
     */
    public function isUser()
    {
        return $this->name === 'user';
    }
}

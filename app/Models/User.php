<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
        'status',
        'photo',
        'address',
        'city',
        'state',
        'post_code',
        'country',
        'phone',
        'last_login_at',
        'last_login_ip',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'last_login_at' => 'datetime',
    ];

    /**
     * Update user's last login information
     */
    public function updateLastLogin()
    {
        $this->last_login_at = now();
        $this->last_login_ip = request()->ip();
        $this->save();
    }

    /**
     * Get the role that the user has
     */
    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Check if the user is admin
     */
    public function isAdmin()
    {
        return $this->role && $this->role->name === 'admin';
    }

    /**
     * Check if the user is editor
     */
    public function isEditor()
    {
        return $this->role && $this->role->name === 'editor';
    }

    /**
     * Check if the user is a regular user
     */
    public function isUser()
    {
        return $this->role && $this->role->name === 'user';
    }

    /**
     * Check if the user is active
     */
    public function isActive()
    {
        return $this->status === 'active';
    }

    /**
     * Check if the user is inactive
     */
    public function isInactive()
    {
        return $this->status === 'inactive';
    }

    /**
     * Check if the user is suspended
     */
    public function isSuspended()
    {
        return $this->status === 'suspended';
    }

    /**
     * Check if the user can manage sites (admin or editor)
     */
    public function canManageSites()
    {
        return $this->isAdmin() || $this->isEditor();
    }

    /**
     * Check if the user can approve other users (admin or editor)
     */
    public function canApproveUsers()
    {
        return $this->isAdmin() || $this->isEditor();
    }

    /**
     * Check if the user can manage roles (admin only)
     */
    public function canManageRoles()
    {
        return $this->isAdmin();
    }
    
    /**
     * Check if the user has a specific permission
     */
    public function hasPermission($permission)
    {
        if (!$this->role) {
            return false;
        }
        
        return $this->role->hasPermission($permission);
    }
    
    /**
     * Check if the user has any of the given permissions
     */
    public function hasAnyPermission(...$permissions)
    {
        if (!$this->role) {
            return false;
        }
        
        foreach ($permissions as $permission) {
            if ($this->hasPermission($permission)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Check if the user has all of the given permissions
     */
    public function hasAllPermissions(...$permissions)
    {
        if (!$this->role) {
            return false;
        }
        
        foreach ($permissions as $permission) {
            if (!$this->hasPermission($permission)) {
                return false;
            }
        }
        
        return true;
    }
}

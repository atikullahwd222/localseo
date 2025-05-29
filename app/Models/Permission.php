<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'slug',
        'description',
        'module',
    ];

    /**
     * Get the roles that own this permission.
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_permission');
    }
}

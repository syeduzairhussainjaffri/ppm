<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'is_system',
    ];

    protected $casts = [
        'is_system' => 'boolean',
    ];

    /**
     * Relationship: Role permissions
     */
    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'permission_role');
    }

    /**
     * Relationship: Role users
     */
    public function users()
    {
        return $this->hasMany(User::class, 'role', 'slug');
    }

    /**
     * Helper to check if role has a permission
     */
    public function hasPermission(string $permissionSlug): bool
    {
        return $this->permissions->contains('slug', $permissionSlug);
    }
}

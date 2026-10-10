<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Role Constants
     */
    public const ROLE_ADMIN = 'admin';
    public const ROLE_MANAGER = 'manager';
    public const ROLE_TECHNICIAN = 'technician';
    public const ROLE_VIEWER = 'viewer';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Check if user is Super Admin.
     */
    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    /**
     * Check if user is Manager.
     */
    public function isManager(): bool
    {
        return $this->role === self::ROLE_MANAGER;
    }

    /**
     * Check if user is Technician.
     */
    public function isTechnician(): bool
    {
        return $this->role === self::ROLE_TECHNICIAN;
    }

    /**
     * Check if user is Viewer.
     */
    public function isViewer(): bool
    {
        return $this->role === self::ROLE_VIEWER;
    }

    /**
     * Check if user has any of the given roles.
     */
    public function hasRole(array|string $roles): bool
    {
        if (is_string($roles)) {
            $roles = array_map('trim', explode(',', $roles));
        }

        return in_array($this->role, $roles, true);
    }

    /**
     * Dynamic Relationship to Role Model.
     */
    public function roleModel()
    {
        return $this->belongsTo(Role::class, 'role', 'slug');
    }

    /**
     * Dynamic Permission Check.
     */
    public function hasPermission(string $permissionSlug): bool
    {
        // Super Admin has all permissions by default
        if ($this->isAdmin()) {
            return true;
        }

        $role = Role::where('slug', $this->role)->with('permissions')->first();
        if (!$role) {
            return false;
        }

        return $role->permissions->contains('slug', $permissionSlug);
    }
}

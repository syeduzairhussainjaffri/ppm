<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'city',
        'phone',
        'email',
        'address',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Relationship: Users assigned to this branch
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }

    /**
     * Relationship: PPM service records under this branch
     */
    public function ppmRecords()
    {
        return $this->hasMany(PpmRecord::class);
    }

    /**
     * Relationship: Customers registered with this branch
     */
    public function customers()
    {
        return $this->hasMany(Customer::class);
    }

    /**
     * Scope for active branches
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'branch_id',
        'name',
        'department',
        'contact_person',
        'contact_no',
        'email',
        'address',
        'notes',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function ppmRecords()
    {
        return $this->hasMany(PpmRecord::class);
    }

    /**
     * Scope customers according to user branch access
     */
    public function scopeForUserBranch($query, ?User $user = null)
    {
        $user = $user ?? auth()->user();

        if (!$user) {
            return $query;
        }

        if ($user->hasAllBranchesAccess()) {
            $selectedBranch = session('selected_branch_id');
            if (!empty($selectedBranch) && $selectedBranch !== 'all') {
                return $query->where(function ($q) use ($selectedBranch) {
                    $q->where('branch_id', $selectedBranch)
                      ->orWhereNull('branch_id');
                });
            }
            return $query;
        }

        return $query->where(function ($q) use ($user) {
            $q->where('branch_id', $user->branch_id)
              ->orWhereNull('branch_id');
        });
    }
}

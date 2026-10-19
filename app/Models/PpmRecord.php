<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PpmRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'branch_id',
        'customer_id',
        'product_id',
        'record_type',
        'entry_date',
        'purchase_order_no',
        'supply_against',
        'customer_name',
        'product_description',
        'equipment_name',
        'model_make',
        'serial_no',
        'department',
        'contact_person',
        'contact_no',
        'installation_date',
        'performed_by',
        'service_type',
        'scope_of_work',
        'next_ppm_date',
        'status',
        'remarks',
    ];

    protected $casts = [
        'entry_date' => 'date',
        'installation_date' => 'date',
        'next_ppm_date' => 'date',
    ];

    /**
     * Relationship to Branch
     */
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Relationship to Customer
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Relationship to Product
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Query Scope: Auto-scope records according to authenticated user's branch access
     */
    public function scopeForUserBranch($query, ?User $user = null)
    {
        $user = $user ?? auth()->user();

        if (!$user) {
            return $query;
        }

        // If user has unrestricted all branches access, check if a specific branch filter is selected in session
        if ($user->hasAllBranchesAccess()) {
            $selectedBranch = session('selected_branch_id');
            if (!empty($selectedBranch) && $selectedBranch !== 'all') {
                return $query->where('branch_id', $selectedBranch);
            }
            return $query;
        }

        // Scoped to the user's assigned branch
        return $query->where(function ($q) use ($user) {
            $q->where('branch_id', $user->branch_id)
              ->orWhereNull('branch_id'); // Allow legacy records before branches were introduced
        });
    }
}

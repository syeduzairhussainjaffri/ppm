<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PpmRecord extends Model
{
    use HasFactory;

    protected $fillable = [
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

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}

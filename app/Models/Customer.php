<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'department',
        'contact_person',
        'contact_no',
        'email',
        'address',
        'notes',
    ];

    public function ppmRecords()
    {
        return $this->hasMany(PpmRecord::class);
    }
}

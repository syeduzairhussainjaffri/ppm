<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'equipment_name',
        'model_make',
        'category',
        'default_ppm_days',
        'description',
    ];

    public function ppmRecords()
    {
        return $this->hasMany(PpmRecord::class);
    }
}

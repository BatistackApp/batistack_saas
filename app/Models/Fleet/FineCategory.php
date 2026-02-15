<?php

namespace App\Models\Fleet;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FineCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'label',
        'antai_code',
        'default_amount',
    ];
}

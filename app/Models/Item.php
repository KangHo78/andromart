<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'name',
        'category_id',
        'branch_id',
        'supplier_id',
        'buy',
        'sell',
        'discount',
        'image',
        'status',
        'keterangan',
        'created_at',
        'updated_at',
    ];
}

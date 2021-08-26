<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'branch_id',
        'identity',
        'name',
        'contact',
        'address',
        'created_at',
        'updated_at',
    ];

    public function branch()
    {
        return $this->belongsTo('App\Models\Branch');
    }
}

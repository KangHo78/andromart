<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AssetAddition extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $table = 'asset_addition';

    protected $fillable = [
        'code',
        'asset_id',
        'branch_id',
        'cash_id',
        'price',
        'date',
        'description',
        'created_at',
        'updated_at',
        'deleted_at',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    public function branch()
    {
        return $this->belongsTo('App\Models\Branch');
    }

    public function asset()
    {
        return $this->belongsTo('App\Models\AccountData');
    }

    public function cash()
    {
        return $this->belongsTo('App\Models\AccountData');
    }
}

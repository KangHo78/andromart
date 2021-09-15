<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'branch_id',
        'identity',
        'name',
        'contact',
        'address',
        'level',
        'gender',
        'birthday',
        'avatar',
        'created_at',
        'updated_at',
        'deleted_at',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

    public function branch()
    {
        return $this->belongsTo('App\Models\Branch');
    }

    public function getAvatar()
    {
        if(!$this->avatar){
            return asset('assetsmaster/avatar/avatar.png');
        }
        return asset('assetsmaster/avatar/'. $this->avatar);
    }
}

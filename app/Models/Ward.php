<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ward extends Model
{
    
    protected $appends = ['value'];

    public function getValueAttribute()
    {
        return $this->name;
    }

    public function userWardPermissions()
    {
        return $this->belongsToMany(User::class, 'user_ward_permissions');
    }

    public function lga()
    {
        return $this->belongsTo(Lga::class);
    }

}

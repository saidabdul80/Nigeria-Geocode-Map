<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
//use EloquentFilter\Filterable;

class Lga extends Model
{
    //use Filterable;

    protected $fillable = [
        'name',
        'state_id',
        'longitude',
        'latitude',
    ];

    public function state()
    {
        return $this->belongsTo(State::class);
    }

    public function wards()
    {
        return $this->hasMany(Ward::class);
    }

    protected $appends = ['value'];

    public function getValueAttribute()
    {
        return $this->name;
    }
}

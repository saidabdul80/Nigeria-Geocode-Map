<?php

namespace App\Models;

//use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Model;

class State extends Model
{
    //use Filterable;
    protected $fillable = [
        'status',
        'name',
    ];

    public function lgas(){
        return $this->hasMany(Lga::class,'state_id');
    }
}

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

}

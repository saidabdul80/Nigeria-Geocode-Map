<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectOutlook extends Model
{
    protected $fillable = [
        'state_id',
        'lga_id',
        'outlook',
        'project_year'
    ];

    protected $casts = [
        'project_year' => 'integer'
    ];

    public function state()
    {
        return $this->belongsTo(State::class);
    }

    public function lga()
    {
        return $this->belongsTo(Lga::class);
    }
}

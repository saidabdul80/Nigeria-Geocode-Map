<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Record extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'state_id',
        'lga_id',
        'ward_id',
        'data'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'data' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    protected $appends = ['record'];
    /**
     * Get the state that owns the record.
     */
    public function state()
    {
        return $this->belongsTo(State::class);
    }

    /**
     * Get the LGA that owns the record.
     */
    public function lga()
    {
        return $this->belongsTo(Lga::class);
    }

    /**
     * Scope for filtering by state
     */
    public function scopeByState($query, $stateId)
    {
        return $query->where('state_id', $stateId);
    }

    /**
     * Scope for filtering by LGA
     */
    public function scopeByLga($query, $lgaId)
    {
        return $query->where('lga_id', $lgaId);
    }

    /**
     * Accessor for getting specific data attributes
     */
    public function getDataAttribute($value)
    {
        return json_decode($value, true);
    }

    /**
     * Mutator for setting data attribute
     */
    public function setDataAttribute($value)
    {
        $this->attributes['data'] = json_encode($value);
    }

    public function getRecordAttribute(){
        return $this->data;
    }
}
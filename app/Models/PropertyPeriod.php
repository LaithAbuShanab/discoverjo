<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PropertyPeriod extends Model
{
    protected $guarded = [];

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    public function availabilityDays()
    {
        return $this->hasMany(PropertyAvailabilityDay::class);
    }

}

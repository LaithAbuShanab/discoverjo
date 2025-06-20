<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PropertyAvailabilityDay extends Model
{
    protected $guarded = [];

    public function availability()
    {
        return $this->belongsTo(PropertyAvailability::class, 'property_availability_id');
    }

    public function period()
    {
        return $this->belongsTo(PropertyPeriod::class, 'property_period_id');
    }

}

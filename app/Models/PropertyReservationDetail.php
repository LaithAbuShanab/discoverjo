<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PropertyReservationDetail extends Model
{
    protected $guarded = [];
    public $timestamps = true;

    public function reservation()
    {
        return $this->belongsTo(PropertyReservation::class, 'property_reservation_id');
    }

    public function period()
    {
        return $this->belongsTo(PropertyPeriod::class,'property_period_id');
    }
}

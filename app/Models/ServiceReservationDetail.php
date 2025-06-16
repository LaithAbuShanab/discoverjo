<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceReservationDetail extends Model
{

    protected $guarded=[];
    public function reservation()
    {
        return $this->belongsTo(ServiceReservation::class, 'service_reservation_id');
    }

    public function priceAge()
    {
        return $this->belongsTo(ServicePriceAge::class, 'price_age_id');
    }

}

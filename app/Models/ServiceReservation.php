<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceReservation extends Model
{
    protected $guarded = [];
    protected $table = 'service_reservations';
    public $timestamps = true;

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function details()
    {
        return $this->hasMany(ServiceReservationDetail::class, 'service_reservation_id');
    }

}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PropertyReservation extends Model
{
    protected $guarded = [];

    protected $table = 'property_reservations';

    public $timestamps = true;

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    public function details()
    {
        return $this->hasMany(PropertyReservationDetail::class);
    }


}

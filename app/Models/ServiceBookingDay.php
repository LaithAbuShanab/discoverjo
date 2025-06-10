<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceBookingDay extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function serviceBooking()
    {
        return $this->belongsTo(ServiceBooking::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GuideTripPriceAge extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function guideTrip()
    {
        return $this->belongsTo(GuideTrip::class, 'guide_trip_id');
    }
}

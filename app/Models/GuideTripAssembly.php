<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class GuideTripAssembly extends Model
{
    use HasFactory, HasTranslations;
    protected $guarded = [];

    public $translatable = ['place'];

    protected $casts = [
        'place' => 'json',
    ];

    public function guideTrip()
    {
        return $this->belongsTo(GuideTrip::class, 'guide_trip_id');
    }
}

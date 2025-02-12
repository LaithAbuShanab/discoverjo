<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class GuideTripActivity extends Model
{
    use HasFactory, HasTranslations;
    protected $guarded = [];

    public $translatable = ['activity'];

    protected $casts = [
        'activity' => 'json',
    ];

    public function guideTrip()
    {
        return $this->belongsTo(GuideTrip::class, 'guide_trip_id');
    }
}

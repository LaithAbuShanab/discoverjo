<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class GuideTripRequirement extends Model
{
    use HasFactory, HasTranslations;
    protected $guarded = [];

    public $translatable = ['item'];

    protected $casts = [
        'item' => 'json',
    ];

    public function guideTrip()
    {
        return $this->belongsTo(GuideTrip::class, 'guide_trip_id');
    }
}

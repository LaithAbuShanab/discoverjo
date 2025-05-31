<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class GuideTripPaymentMethod extends Model
{
    use HasFactory, HasTranslations;
    protected $guarded = [];

    public $translatable = ['method'];

    protected $casts = [
        'method' => 'json',
    ];

    public function guideTrip()
    {
        return $this->belongsTo(GuideTrip::class);
    }
}

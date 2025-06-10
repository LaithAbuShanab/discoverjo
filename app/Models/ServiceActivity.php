<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class ServiceActivity extends Model
{
    use HasFactory, HasTranslations;
    protected $guarded = [];

    public $translatable = ['activity'];

    protected $casts = [
        'activity' => 'json',
    ];

    public function service()
    {
        return $this->belongsTo(Service::class, 'service_id');
    }
}

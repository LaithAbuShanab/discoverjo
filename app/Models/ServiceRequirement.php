<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class ServiceRequirement extends Model
{
    use HasFactory, HasTranslations;
    protected $guarded = [];

    public $translatable = ['item'];

    protected $casts = [
        'item' => 'json',
    ];

    public function service()
    {
        return $this->belongsTo(Service::class, 'service_id');
    }
}

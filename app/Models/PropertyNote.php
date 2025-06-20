<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class PropertyNote extends Model
{
    use HasFactory, HasTranslations;
    protected $guarded = [];

    public $translatable = ['note'];

    protected $casts = [
        'note' => 'json',
    ];

    public function property()
    {
        return $this->belongsTo(Property::class, 'property_id');
    }

}

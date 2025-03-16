<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class PlanActivity extends Model
{
    use HasFactory, HasTranslations;

    public $guarded = [];

    public $translatable = ['activity_name', 'notes'];

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    public function place()
    {
        return $this->belongsTo(Place::class);
    }
}

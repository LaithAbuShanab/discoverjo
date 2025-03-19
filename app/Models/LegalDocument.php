<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Translatable\HasTranslations;

class LegalDocument extends Model
{
    use HasFactory, HasTranslations,LogsActivity;
    public $translatable = ['title', 'content'];

    protected $guarded = [];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('legal Document');
    }
    public function terms()
    {
        return $this->hasMany(Term::class);
    }
}

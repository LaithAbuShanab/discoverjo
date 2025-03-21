<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;
use Spatie\Translatable\HasTranslations;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Plan extends Model
{
    use HasFactory, HasTranslations, HasSlug , LogsActivity;

    public $translatable = ['name', 'description'];
    public $guarded = [];

    protected $casts = [
        'activity_name' => 'array',
        'notes' => 'array',
    ];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom(function () {
                return $this->getTranslation('name', 'en');
            })
            ->saveSlugsTo('slug')
            ->usingLanguage('en');
    }

    public function creator()
    {
        return $this->morphTo();
    }

    public function days()
    {
        return $this->hasMany(PlanDay::class);
    }

    public function favoritedBy()
    {
        return $this->morphToMany(User::class, 'favorable');
    }

    public function posts()
    {
        return $this->morphMany(Post::class, 'visitable');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('plan');
    }
}

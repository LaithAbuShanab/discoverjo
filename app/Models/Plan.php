<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;
use Spatie\Translatable\HasTranslations;

class Plan extends Model
{
    use HasFactory, HasTranslations, HasSlug;

    public $translatable = ['name', 'description'];
    public $guarded = [];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom(function () {
                return app()->getLocale() === 'en' ? $this->getTranslation('name', 'en') : $this->slug;
            })
            ->saveSlugsTo('slug');
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
}

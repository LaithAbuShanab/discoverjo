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
        $slugOptions = SlugOptions::create()
            ->generateSlugsFrom(function () {
                $name = $this->getTranslation('name', 'en') ?? $this->getTranslation('name', app()->getLocale());
                return !empty($name) ? $name : 'default-slug';
            })->saveSlugsTo('slug');

        // Only generate the slug when creating (not updating)
        if ($this->exists) {
            $slugOptions->doNotGenerateSlugsOnUpdate();
        }

        return $slugOptions;
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
            ->useLogName('trip');
    }
}

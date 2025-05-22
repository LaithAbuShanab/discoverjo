<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;
use Spatie\Translatable\HasTranslations;

class Volunteering extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia, HasTranslations,HasSlug,LogsActivity;

    public $translatable = ['name', 'description', 'address'];
    public $guarded = [];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('volunteering');
    }
    public function region()
    {
        return $this->belongsTo(Region::class);
    }
    public function organizers()
    {
        return $this->morphToMany(Organizer::class, 'organizerable');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('volunteering')
            ->singleFile()
            ->registerMediaConversions(function (Media $media) {
                $this->addMediaConversion('volunteering_app')->format('webp')->nonQueued();
            });
    }

    public function interestedUsers()
    {
        return $this->morphToMany(User::class, 'interestable')->withTimestamps();
    }

    public function reviews(): MorphMany
    {
        return $this->morphMany(Reviewable::class, 'reviewable')->latest();
    }

    public function posts()
    {
        return $this->morphMany(Post::class, 'visitable');
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom(function () {
                return app()->getLocale() === 'en' ? $this->getTranslation('name', 'en') : $this->slug;
            })
            ->saveSlugsTo('slug')
            ->doNotGenerateSlugsOnUpdate(); // This prevents slug regeneration on updates
    }

    public function favoritedBy()
    {
        return $this->morphToMany(User::class, 'favorable', 'favorables')->withTimestamps();
    }
}

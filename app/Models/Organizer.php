<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;
use Spatie\Translatable\HasTranslations;

class Organizer extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia, HasTranslations, HasSlug;

    public $translatable = ['name'];
    public $guarded = [];

    public function organizerables()
    {
        return $this->morphedByMany(Event::class, 'organizerables');
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

    //    public function volunteeringOrganizerables()
    //    {
    //        return $this->morphedByMany(Volunteering::class, 'organizerables');
    //    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('organizer')
            ->singleFile()
            ->registerMediaConversions(function (Media $media) {
                $this->addMediaConversion('organizer_app')->format('webp')->nonQueued();;
            });
    }
}

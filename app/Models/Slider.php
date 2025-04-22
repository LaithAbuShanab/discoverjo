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

class Slider extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia, HasSlug;

    use HasTranslations;

    public $translatable = ['title','content'];
    public $guarded = [];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('slider')
            ->singleFile()
            ->registerMediaConversions(function (Media $media) {
                $this->addMediaConversion('slider_app')->width(1042)->height(1042)->format('webp')->nonQueued();
                $this->addMediaConversion('slider_website')->width(1042)->height(1042)->format('webp')->nonQueued();
            });
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom(function () {
                return app()->getLocale() === 'en' ? $this->getTranslation('title', 'en') : $this->slug;
            })
            ->saveSlugsTo('slug')
            ->doNotGenerateSlugsOnUpdate(); // This prevents slug regeneration on updates
    }
}

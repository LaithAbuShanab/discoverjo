<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;


class Feature extends Model implements HasMedia
{
    use HasFactory, HasTranslations, InteractsWithMedia, HasSlug;

    public $translatable = ['name'];
    public $guarded = [];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom(function () {
                return app()->getLocale() === 'en' ? $this->getTranslation('name', 'en') : $this->slug;
            })
            ->saveSlugsTo('slug');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('feature_active')
            ->singleFile()
            ->registerMediaConversions(function (Media $media) {
                $this->addMediaConversion('feature_active_app')->width(80)->height(80)->format('webp')->nonQueued();
                $this->addMediaConversion('feature_active_website')->width(250)->height(250)->format('webp')->nonQueued();
            });

        $this->addMediaCollection('feature_inactive')
            ->singleFile()
            ->registerMediaConversions(function (Media $media) {
                $this->addMediaConversion('feature_inactive_app')->width(80)->height(80)->format('webp')->nonQueued();
                $this->addMediaConversion('feature_inactive_website')->width(250)->height(250)->format('webp')->nonQueued();
            });
    }

    public function places()
    {
        return $this->belongsToMany(Place::class);
    }
}

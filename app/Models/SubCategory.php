<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Translatable\HasTranslations;

class SubCategory extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia, HasTranslations;

    public $translatable = ['name'];
    public $guarded = [];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('subcategory_active')
            ->singleFile()
            ->registerMediaConversions(function (Media $media) {
                $this->addMediaConversion('subcategory_active_app')->format('webp')->nonQueued();
            });

        $this->addMediaCollection('subcategory_inactive')
            ->singleFile()
            ->registerMediaConversions(function (Media $media) {
                $this->addMediaConversion('subcategory_inactive_app')->format('webp')->nonQueued();
            });
    }


    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function places()
    {
        return $this->hasMany(Place::class);
    }
}

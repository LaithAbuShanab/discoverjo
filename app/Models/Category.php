<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;
use Spatie\Translatable\HasTranslations;

class Category extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia, HasSlug,LogsActivity;

    use HasTranslations;

    public $translatable = ['name'];
    public $guarded = [];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('category');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('main_category')
            ->singleFile()
            ->registerMediaConversions(function (Media $media) {
                $this->addMediaConversion('main_category_app')->format('webp')->nonQueued();
            });

        $this->addMediaCollection('category_active')
            ->singleFile()
            ->registerMediaConversions(function (Media $media) {
                $this->addMediaConversion('category_active_app')->format('webp')->nonQueued();
            });

        $this->addMediaCollection('category_inactive')
            ->singleFile()
            ->registerMediaConversions(function (Media $media) {
                $this->addMediaConversion('category_inactive_app')->format('webp');
            });
    }

    /**
     * Get the parent category.
     */
    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    /**
     * Get the child categories.
     */
    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    /**
     * Get all descendants of the category.
     */
    public function descendants()
    {
        return $this->children()->with('descendants');
    }

    /**
     * Get all ancestors of the category.
     */
    public function ancestors()
    {
        return $this->parent ? $this->parent->ancestors()->prepend($this->parent) : collect([$this]);
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

    public function places()
    {
        return $this->belongsToMany(Place::class, 'place_categories');
    }
}

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

class GuideTrip extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia, HasTranslations, HasSlug,LogsActivity;
    protected $guarded = [];

    public $translatable = ['name', 'description'];

    protected $casts = [
        'name' => 'json',
        'description' => 'json',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('Guide trip');
    }
    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom(function () {
                return $this->getTranslation('name', 'en');
            })
            ->saveSlugsTo('slug')
            ->usingLanguage('en');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('guide_trip_gallery')

            ->registerMediaConversions(function (Media $media) {
                // Only perform conversion for image files, skip for video
                if (in_array($media->mime_type, ['image/jpeg', 'image/png', 'image/bmp', 'image/gif', 'image/svg+xml', 'image/webp'])) {
                    $this->addMediaConversion('guide_trip_gallery_app')
                        ->width(450)
                        ->height(450)
                        ->format('webp')->nonQueued();
                    $this->addMediaConversion('guide_trip_gallery_website')
                        ->width(450)
                        ->height(450)
                        ->format('webp')->nonQueued();
                }
            });
    }

    public function guide()
    {
        return $this->belongsTo(User::class, 'guide_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'guide_id');
    }

    public function activities()
    {
        return $this->hasMany(GuideTripActivity::class, 'guide_trip_id');
    }

    public function priceIncludes()
    {
        return $this->hasMany(GuideTripPriceInclude::class, 'guide_trip_id');
    }

    public function priceAges()
    {
        return $this->hasMany(GuideTripPriceAge::class, 'guide_trip_id');
    }

    public function assemblies()
    {
        return $this->hasMany(GuideTripAssembly::class, 'guide_trip_id');
    }

    public function requirements()
    {
        return $this->hasMany(GuideTripRequirement::class, 'guide_trip_id');
    }

    public function trail()
    {
        return $this->hasOne(GuideTripTrail::class, 'guide_trip_id');
    }

    // Define the relationship with the GuideTripUser model
    public function guideTripUsers()
    {
        return $this->hasMany(GuideTripUser::class);
    }

    // Define a relationship with the User model through the GuideTripUser model
    public function users()
    {
        return $this->hasManyThrough(User::class, GuideTripUser::class, 'guide_trip_id', 'id', 'id', 'user_id');
    }

    public function reviews(): MorphMany
    {
        return $this->morphMany(Reviewable::class, 'reviewable');
    }
}

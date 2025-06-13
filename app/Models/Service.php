<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Sluggable\SlugOptions;

class Service extends Model implements HasMedia
{
    use \Illuminate\Database\Eloquent\Factories\HasFactory, \Spatie\MediaLibrary\InteractsWithMedia, \Spatie\Translatable\HasTranslations, \Spatie\Sluggable\HasSlug;

    public $translatable = ['name', 'description', 'address'];
    public $guarded = [];
    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom(function () {
                return app()->getLocale() === 'en' ? $this->getTranslation('name', 'en') : $this->slug;
            })
            ->saveSlugsTo('slug')
            ->doNotGenerateSlugsOnUpdate();
    }

    public function region()
    {
        return $this->belongsTo(Region::class);
    }

    public function features()
    {
        return $this->belongsToMany(Feature::class, 'service_features');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('main_service')
            ->singleFile()
            ->registerMediaConversions(function (Media $media) {
                $this->addMediaConversion('main_service_app')->format('webp')->nonQueued();
            });

        $this->addMediaCollection('service_gallery')
            ->registerMediaConversions(function (Media $media) {
                $this->addMediaConversion('service_gallery_app')->format('webp')->nonQueued();
            });
    }

    public function categories()
    {
        return $this->belongsToMany(ServiceCategory::class, 'service_service_categories');
    }

    public function requirements()
    {
        return $this->hasMany(ServiceRequirement::class, 'service_id');
    }

    public function priceAges()
    {
        return $this->hasMany(ServicePriceAge::class, 'service_id');
    }

    public function activities()
    {
        return $this->hasMany(ServiceActivity::class, 'service_id');
    }

    public function notes()
    {
        return $this->hasMany(ServiceNote::class, 'service_id');
    }

    public function serviceBookings()
    {
        return $this->hasMany(ServiceBooking::class, 'service_id');
    }

    public function serviceBookingDays()
    {
        return $this->hasManyThrough(ServiceBookingDay::class, ServiceBooking::class, 'service_id', 'service_booking_id');
    }

    public function provider()
    {
        return $this->morphTo();
    }

}

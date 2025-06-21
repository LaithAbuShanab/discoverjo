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

class Property extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia, HasTranslations, HasSlug, LogsActivity;
    protected $guarded = [];

    public $translatable = ['name', 'description','address'];
    protected static $logAttributes = ['slug', 'name', 'description','status'];
    protected static $logOnlyDirty = true;
    protected static $logName = 'Property';
    protected static $recordEvents = ['created', 'updated', 'deleted'];
    protected $casts = [
        'name' => 'json',
        'description' => 'json',
        'address' => 'json',
    ];
    public function getDescriptionForEvent(string $eventName): string
    {
        return "A user has been {$eventName}";
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('property')
            ->logOnly(['slug', 'name', 'description', 'status'])
            ->logOnlyDirty();
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom(function () {
                return $this->getTranslation('name', 'en');
            })
            ->saveSlugsTo('slug')
            ->usingLanguage('en')
            ->doNotGenerateSlugsOnUpdate(); // This prevents slug regeneration on updates
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('main_property_image')
            ->singleFile()
            ->registerMediaConversions(function (Media $media) {
                $this->addMediaConversion('main_property_image_app')->format('webp')->nonQueued();
            });
        $this->addMediaCollection('property_gallery')

            ->registerMediaConversions(function (Media $media) {
                $this->addMediaConversion('property_gallery_app')
                    ->format('webp')->nonQueued();
            });
    }

    public function host()
    {
        return $this->belongsTo(User::class, 'host_id');
    }

    public function region()
    {
        return $this->belongsTo(Region::class, 'region_id');
    }

    public function periods()
    {
        return $this->hasMany(PropertyPeriod::class);
    }

    public function availabilities()
    {
        return $this->hasMany(PropertyAvailability::class);
    }

    public function availabilityDays()
    {
        return $this->hasManyThrough(PropertyAvailabilityDay::class, PropertyAvailability::class);
    }

    public function notes()
    {
        return $this->hasMany(PropertyNote::class, 'property_id');
    }

    public function amenities()
    {
        return $this->belongsToMany(Amenity::class, 'property_amenities')->whereNotNull('parent_id');
    }

    public function reviews(): MorphMany
    {
        return $this->morphMany(Reviewable::class, 'reviewable')->latest();
    }

}

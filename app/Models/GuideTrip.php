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
    use HasFactory, InteractsWithMedia, HasTranslations, HasSlug, LogsActivity;
    protected $guarded = [];

    public $translatable = ['name', 'description'];
    protected static $logAttributes = ['slug', 'name', 'description', 'start_datetime', 'end_datetime', 'main_price', 'max_attendance', 'status'];
    protected static $logOnlyDirty = true;
    protected static $logName = 'guide trip';
    protected static $recordEvents = ['created', 'updated', 'deleted'];
    protected $casts = [
        'name' => 'json',
        'description' => 'json',
    ];
    public function getDescriptionForEvent(string $eventName): string
    {
        return "A user has been {$eventName}";
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('guide trip')
            ->logOnly(['slug', 'name', 'description', 'start_datetime', 'end_datetime', 'main_price', 'max_attendance', 'status'])
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
        $this->addMediaCollection('guide_trip_gallery')

            ->registerMediaConversions(function (Media $media) {
                $this->addMediaConversion('guide_trip_gallery_app')
                    ->format('webp')->nonQueued();
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

    public function requestGuideTripUsers()
    {
        return $this->hasMany(GuideTripUser::class)
            ->where('status', 0)
            ->whereHas('user', function ($query) {
                $query->where('status', 1);
            });
    }

    // Define a relationship with the User model through the GuideTripUser model
    public function users()
    {
        return $this->hasManyThrough(User::class, GuideTripUser::class, 'guide_trip_id', 'id', 'id', 'user_id');
    }

    public function reviews(): MorphMany
    {
        return $this->morphMany(Reviewable::class, 'reviewable')->latest();
    }

    public function favoritedBy()
    {
        return $this->morphToMany(User::class, 'favorable', 'favorables')->withTimestamps();
    }
}

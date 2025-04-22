<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Translatable\HasTranslations;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Place extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia, HasTranslations, HasSlug, LogsActivity;

    public $translatable = ['name', 'description', 'address'];
    public $guarded = [];

    protected static $logAttributes = ['slug','name','description','google_map_url','price_level','website','rating','status'];
    protected static $logOnlyDirty = true;
    protected static $logName = 'place';
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
            ->useLogName('place')
            ->logOnly(['slug','name','description','google_map_url','price_level','website','rating','status'])
            ->logOnlyDirty();
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

    public function region()
    {
        return $this->belongsTo(Region::class);
    }

    public function openingHours()
    {
        return $this->hasMany(OpeningHour::class);
    }

    public function popularPlaces()
    {
        return $this->hasOne(PopularPlace::class);
    }

    public function topTenPlaces()
    {
        return $this->hasOne(TopTen::class);
    }

    public function tags()
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }

    public function features()
    {
        return $this->belongsToMany(Feature::class);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('main_place')
            ->singleFile()
            ->registerMediaConversions(function (Media $media) {
                $this->addMediaConversion('main_place_app')->width(450)->height(450)->format('webp')->nonQueued();
                $this->addMediaConversion('main_place_website')->width(1124)->height(1124)->format('webp')->nonQueued();
            });

        $this->addMediaCollection('place_gallery')
            ->registerMediaConversions(function (Media $media) {
                $this->addMediaConversion('place_gallery_app')->width(450)->height(450)->format('webp')->nonQueued();
                $this->addMediaConversion('place_gallery_website')->width(1124)->height(1124)->format('webp')->nonQueued();
            });
    }

    public function placesThrough()
    {
        return Place::whereIn('id', function ($query) {
            $query->select('place_id')
                ->from('places_categories')
                ->whereIn('category_id', function ($subQuery) {
                    $subQuery->select('id')
                        ->from('categories')
                        ->where('parent_id', $this->id)
                        ->orWhere('id', $this->id);
                });
        });
    }

    public function favoritedBy()
    {
        return $this->morphToMany(User::class, 'favorable', 'favorables')->withTimestamps();
    }


    public function visitors()
    {
        return $this->belongsToMany(User::class, 'visited_places', 'place_id', 'user_id');
    }

    public function reviews(): MorphMany
    {
        return $this->morphMany(Reviewable::class, 'reviewable');
    }

    public function posts()
    {
        return $this->morphMany(Post::class, 'visitable');
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'place_categories');
    }

//    public function activities()
//    {
//        return $this->hasMany(PlanActivity::class);
//    }
}

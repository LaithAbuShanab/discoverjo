<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Translatable\HasTranslations;

class Event extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia, HasTranslations;

    public $translatable = ['name', 'description', 'address'];
    public $guarded = [];

    public function region()
    {
        return $this->belongsTo(Region::class);
    }

    public function organizers()
    {
        return $this->morphToMany(Organizer::class, 'organizerable');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('event')
            ->singleFile()
            ->registerMediaConversions(function (Media $media) {
                $this->addMediaConversion('event_app')->width(450)->height(350)->format('webp')->nonQueued();
                $this->addMediaConversion('event_website')->width(250)->height(250)->format('webp')->nonQueued();
            });
    }

    public function interestedUsers()
    {
        return $this->morphToMany(User::class, 'interestable')->withTimestamps();
    }

    public function reviews(): MorphMany
    {
        return $this->morphMany(Reviewable::class, 'reviewable');
    }

    public function posts()
    {
        return $this->morphMany(Post::class, 'visitable');
    }
}
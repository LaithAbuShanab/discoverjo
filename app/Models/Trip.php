<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Trip extends Model
{
    use HasFactory, HasSlug;

    protected $guarded = [];
    protected $table = 'trips';

    protected $casts = [
        'age_range' => 'array',
    ];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug');
    }

    public function place()
    {
        return $this->belongsTo(Place::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function tags()
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }

    public function gender()
    {
        switch ($this->sex) {
            case 0:
                return __('app.both');
            case 1:
                return __('app.male');
            case 2:
                return __('app.female');
        }
    }

    public function usersTrip()
    {
        return $this->hasMany(UsersTrip::class);
    }

    public function reviews(): MorphMany
    {
        return $this->morphMany(Reviewable::class, 'reviewable');
    }

    public function posts()
    {
        return $this->morphMany(Post::class, 'visitable');
    }

    public function conversation()
    {
        return $this->belongsTo(Conversation::class, 'id', 'trip_id');
    }
}

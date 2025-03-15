<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Trip extends Model
{
    use HasFactory, HasSlug, LogsActivity;

    protected $guarded = [];
    protected $table = 'trips';

    public function getSlugOptions(): SlugOptions
    {
        $slugOptions = SlugOptions::create()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug');

        // Only generate the slug when creating (not updating)
        if ($this->exists) {
            $slugOptions->doNotGenerateSlugsOnUpdate();
        }

        return $slugOptions;
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

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('trip');
    }
}

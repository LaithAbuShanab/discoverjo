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
    public $translatable = ['name', 'description'];
    protected static $logAttributes = ['place_id','trip_type','name','description','cost','age_range','sex','date_time','attendance_number','status'];
    protected static $logOnlyDirty = true;
    protected static $logName = 'trip';
    protected static $recordEvents = ['created', 'updated', 'deleted'];

    public function getDescriptionForEvent(string $eventName): string
    {
        return "A user has been {$eventName}";
    }
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('trip')
            ->logOnly(['place_id','trip_type','name','description','cost','age_range','sex','date_time','attendance_number','status'])
            ->logOnlyDirty();
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug')
            ->doNotGenerateSlugsOnUpdate(); // This prevents slug regeneration on updates
    }

    public function place()
    {
        return $this->belongsTo(Place::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
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

    public function favoritedBy()
    {
        return $this->morphToMany(User::class, 'favorable', 'favorables')->withTimestamps();
    }

}

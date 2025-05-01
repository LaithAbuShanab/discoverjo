<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Post extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia,LogsActivity;
    protected $guarded = [];
    protected static $logAttributes = ['visitable_type','visitable_id','content','privacy'];
    protected static $logOnlyDirty = true;
    protected static $logName = 'post';
    protected static $recordEvents = ['created', 'updated', 'deleted'];

    public function getDescriptionForEvent(string $eventName): string
    {
        return "A user has been {$eventName}";
    }
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['visitable_type','visitable_id','content','privacy'])
            ->logOnlyDirty()
            ->useLogName('post');
    }
    public function place()
    {
        return $this->belongsTo(Place::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('post')
            ->registerMediaConversions(function (Media $media) {
                $this->addMediaConversion('post_app')->width(295)->height(220)->format('webp')->nonQueued();
                $this->addMediaConversion('post_website')->width(400)->height(365)->format('webp')->nonQueued();
            });
    }

    public function visitable()
    {
        return $this->morphTo();
    }

    public function likes()
    {
        return $this->morphMany(PostLike::class, 'likable');
    }

    public function comments()
    {
        return $this->hasMany(Comment::class)->whereNull('parent_id');
    }
}

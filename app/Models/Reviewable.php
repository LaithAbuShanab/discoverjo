<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Reviewable extends Model
{
    use HasFactory, LogsActivity;

    protected $table = 'reviewables';

    protected $guarded = [];
    protected static $logAttributes = ['rating', 'comment'];
    protected static $logOnlyDirty = true;
    protected static $logName = 'review';
    protected static $recordEvents = ['created', 'updated', 'deleted'];

    public function getDescriptionForEvent(string $eventName): string
    {
        return "A user has been {$eventName}";
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('review')
            ->logOnly(['rating', 'comment'])
            ->logOnlyDirty();
    }

    public function reviewable()
    {
        return $this->morphTo();
    }

    public function like()
    {
        return $this->belongsToMany(User::class, 'review_likes', 'review_id', 'user_id')->withPivot('status');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function likeDislike()
    {
        return $this->hasMany(ReviewLike::class, 'review_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Follow extends Model
{
    use HasFactory, LogsActivity;
    protected $guarded = [];
    protected static $logAttributes = ['following_id'];
    protected static $logOnlyDirty = true;
    protected static $logName = 'follow';
    protected static $recordEvents = ['created', 'updated', 'deleted'];

    public function getDescriptionForEvent(string $eventName): string
    {
        return "A user has been {$eventName}";
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('follow')
            ->logOnly(['following_id'])
            ->logOnlyDirty();
    }

    public function followingUser()
    {
        return $this->belongsTo(User::class, 'following_id');
    }

    public function followerUser()
    {
        return $this->belongsTo(User::class, 'follower_id');
    }
}

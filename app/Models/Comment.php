<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Comment extends Model
{
    use HasFactory,LogsActivity;
    protected $guarded =[];


    protected static $logAttributes = ['content'];
    protected static $logOnlyDirty = true;
    protected static $logName = 'comment';
    protected static $recordEvents = ['created', 'updated', 'deleted'];

    public function getDescriptionForEvent(string $eventName): string
    {
        return "A user has been {$eventName}";
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('comment')
            ->logOnly(['content'])
            ->logOnlyDirty();
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function post()
    {
        return $this->belongsTo(Post::class);
    }
    public function likes()
    {
        return $this->morphMany(PostLike::class, 'likable');
    }

    // A comment can have many replies
    public function replies()
    {
        return $this->hasMany(Comment::class, 'parent_id');
    }

    // A reply belongs to a parent comment
    public function parent()
    {
        return $this->belongsTo(Comment::class, 'parent_id');
    }

}

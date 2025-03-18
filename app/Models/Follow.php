<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Follow extends Model
{
    use HasFactory,LogsActivity;
    protected $guarded = [];
    protected static $recordEvents = ['created', 'updated', 'deleted'];
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('follow');
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

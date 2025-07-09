<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class RatingGuide extends Model
{
    use HasFactory, LogsActivity;
    protected $guarded = [];

    protected static $logAttributes = ['guide_id', 'rating'];
    protected static $logOnlyDirty = true;
    protected static $logName = 'guide rating';
    protected static $recordEvents = ['created', 'updated', 'deleted'];

    public function getDescriptionForEvent(string $eventName): string
    {
        return "A user has been {$eventName}";
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('guide rating')
            ->logOnlyDirty()
            ->useLogName('guide rating');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function guide()
    {
        return $this->belongsTo(User::class, 'guide_id')->where('type', 2);
    }
}

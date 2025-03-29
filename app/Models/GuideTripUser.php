<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Sluggable\HasSlug;

class GuideTripUser extends Model
{
    use HasFactory,LogsActivity;
    protected $guarded = [];
    protected static $logAttributes = ['guide_trip_id','first_name','last_name','phone_number','age','status'];
    protected static $logOnlyDirty = true;
    protected static $logName = 'guide trip';
    protected static $recordEvents = ['created', 'updated', 'deleted'];
    public function getDescriptionForEvent(string $eventName): string
    {
        return "A user has been {$eventName}";
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('guide trip')
            ->logOnly(['guide_trip_id','first_name','last_name','phone_number','age','status'])
            ->logOnlyDirty();
    }
    // Define the relationship with the User model
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Define the relationship with the GuideTrip model
    public function guideTrip()
    {
        return $this->belongsTo(GuideTrip::class);
    }
}

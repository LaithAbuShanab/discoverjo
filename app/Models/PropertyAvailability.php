<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PropertyAvailability extends Model
{
    protected $guarded = [];

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function availabilityDays()
    {
        return $this->hasMany(PropertyAvailabilityDay::class);
    }

}

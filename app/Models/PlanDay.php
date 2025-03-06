<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlanDay extends Model
{
    protected $table = 'plan_days';

    protected $fillable = [
        'plan_id',
        'day',
    ];

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    public function activities()
    {
        return $this->hasMany(PlanActivity::class);
    }
}

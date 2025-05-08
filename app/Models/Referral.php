<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Referral extends Model
{
    use HasFactory;

    protected $fillable = [
        'referrer_id',
        'referred_id',
        'referral_code',
    ];

    // The user who sent the referral
    public function referrer()
    {
        return $this->belongsTo(User::class, 'referrer_id');
    }

    // The user who was referred
    public function referred()
    {
        return $this->belongsTo(User::class, 'referred_id');
    }
}

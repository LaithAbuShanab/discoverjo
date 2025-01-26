<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reviewable extends Model
{
    use HasFactory;

    protected $table = 'reviewables';

    protected $guarded = [];

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

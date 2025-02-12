<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Translatable\HasTranslations;

class Plan extends Model
{
    use HasFactory,  HasTranslations;

    public $translatable = ['name', 'description'];
    public $guarded = [];

    public function creator()
    {
        return $this->morphTo();
    }

    public function activities()
    {
        return $this->hasMany(PlanActivity::class);
    }

    public function favoritedBy()
    {
        return $this->morphToMany(User::class, 'favorable');
    }

    public function posts()
    {
        return $this->morphMany(Post::class, 'visitable');
    }
}

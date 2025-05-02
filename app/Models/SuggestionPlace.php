<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class SuggestionPlace extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;
    protected $guarded =[];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('suggestion_place')
            ->registerMediaConversions(function (Media $media) {
                $this->addMediaConversion('suggestion_place_app')->format('webp')->nonQueued();
            });
    }
}

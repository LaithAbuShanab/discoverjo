<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class ContactUs extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;
    protected $guarded =[];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('contact')
            ->registerMediaConversions(function (Media $media) {
                $this->addMediaConversion('contact_app')->width(500)->height(500)->format('webp')->nonQueued();
            });
    }
}

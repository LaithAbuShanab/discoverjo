<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Admin extends Authenticatable implements FilamentUser, HasMedia
{
    use Notifiable, HasRoles, InteractsWithMedia;

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    public function canAccessPanel(\Filament\Panel $panel): bool
    {
        return true;
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('admin_profile')->singleFile()->registerMediaConversions(function (Media $media) {
            $this->addMediaConversion('image')->width(250)->height(250)->format('webp')->nonQueued();
        });
    }
}

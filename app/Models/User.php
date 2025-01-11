<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Permission\Traits\HasRoles;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail, HasMedia
{
    use \Spatie\MediaLibrary\InteractsWithMedia;
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'username',
        'birthday',
        'facebook_id',
        'google_id',
        'sex',
        'email',
        'description',
        'email_verified_at',
        'password',
        'lang',
        'phone_number',
        'points',
        'longitude',
        'latitude',
        'status',
        'is_guide',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('avatar')
            ->singleFile()
            ->registerMediaConversions(function (Media $media) {
                $this->addMediaConversion('avatar_app')->width(250)->height(250)->format('webp')->nonQueued();
                $this->addMediaConversion('avatar_website')->width(250)->height(250)->format('webp')->nonQueued();
            });

        $this->addMediaCollection('file')
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/bmp', 'image/gif', 'image/svg+xml', 'image/webp', 'application/pdf'])
            ->registerMediaConversions(function (Media $media) {
                // Only perform conversion for image files, skip for PDFs
                if (in_array($media->mime_type, ['image/jpeg', 'image/png', 'image/bmp', 'image/gif', 'image/svg+xml', 'image/webp'])) {
                    $this->addMediaConversion('avatar_app')->width(250)->height(250)->format('webp')->nonQueued();
                }
            });
    }
}

<?php

namespace App\Models;

use App\Notifications\Admin\AdminRestPasswordNotification;
use App\Notifications\Admin\AdminEmailVerificationNotification;
use Filament\Models\Contracts\FilamentUser;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;



class Admin extends Authenticatable implements FilamentUser, HasMedia
{
    use Notifiable, HasRoles, InteractsWithMedia;
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new AdminRestPasswordNotification($token));
    }


    //must verify email not work
    public function sendEmailVerificationNotification()
    {
        $this->notify(new AdminEmailVerificationNotification);
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'lang',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
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


    public function plans()
    {
        return $this->morphMany('App\Models\Plan', 'creator');
    }
}

<?php

namespace App\Models;

use App\Notifications\Users\UserEmailVerificationNotification;
use App\Notifications\Users\UserResetPasswordNotification;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Permission\Traits\HasRoles;
use Laravel\Passport\HasApiTokens;
use Spatie\Translatable\HasTranslations;

class User extends Authenticatable implements MustVerifyEmail, HasMedia
{
    use \Spatie\MediaLibrary\InteractsWithMedia;
    use HasApiTokens, HasFactory, Notifiable, HasRoles, HasTranslations;
    public $translatable = ['address'];
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

    public function plans()
    {
        return $this->morphMany('App\Models\Plan', 'creator');
    }


    public function sendEmailVerificationNotification()
    {
        $this->notify(new UserEmailVerificationNotification());
    }

    public function sendPasswordResetNotification($token)
    {
        $this->notify(new UserResetPasswordNotification($token));
    }



    public function findForPassport($username)
    {
        return $this->where('email', $username)->orWhere('username', $username)->first();
    }

    public function favoritePlaces()
    {
        return $this->morphedByMany(Place::class, 'favorable');
    }

    public function favoritePlans()
    {
        return $this->morphedByMany(Plan::class, 'favorable');
    }

    public function favoritePosts()
    {
        return $this->morphedByMany(Post::class, 'favorable');
    }

    public function visitedPlace()
    {
        return $this->belongsToMany(Place::class, 'visited_places', 'user_id', 'place_id');
    }

    public function eventInterestables()
    {
        return $this->morphedByMany(Event::class, 'interestable')->withTimestamps();
    }

    public function volunteeringInterestables()
    {
        return $this->morphedByMany(Volunteering::class, 'interestable')->withTimestamps();
    }

    public function favoriteEvent()
    {
        return $this->morphedByMany(Event::class, 'favorable')->withTimestamps();
    }

    public function favoriteVolunteering()
    {
        return $this->morphedByMany(Volunteering::class, 'favorable')->withTimestamps();
    }

    public function favoriteTrip()
    {
        return $this->morphedByMany(Trip::class, 'favorable')->withTimestamps();
    }

    public function favoriteGuideTrip()
    {
        return $this->morphedByMany(GuideTrip::class, 'favorable')->withTimestamps();
    }

    public function reviewTrip()
    {
        return $this->morphedByMany(Trip::class, 'reviewable')->withTimestamps();
    }

    public function reviewGuideTrip()
    {
        return $this->morphedByMany(GuideTrip::class, 'reviewable')->withTimestamps();
    }

    public function reviewPlace()
    {
        return $this->morphedByMany(Place::class, 'reviewable')->withTimestamps();
    }

    public function reviewEvent()
    {
        return $this->morphedByMany(Event::class, 'reviewable')->withTimestamps();
    }

    public function reviewVolunteering()
    {
        return $this->morphedByMany(Volunteering::class, 'reviewable')->withTimestamps();
    }

    public function likeReview()
    {
        return $this->belongsTo(ReviewLike::class, 'user_id');
    }

    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    public function tags()
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }

    public function DeviceToken()
    {
        return $this->hasOne(DeviceToken::class, 'user_id');
    }

    public function followers()
    {
        return $this->belongsToMany(User::class, 'follows', 'following_id', 'follower_id')
            ->withTimestamps()
            ->withPivot('status');
    }

    public function following()
    {
        return $this->belongsToMany(User::class, 'follows', 'follower_id', 'following_id')
            ->withTimestamps()
            ->withPivot('status');
    }

    public function acceptedFollowing()
    {
        return $this->following()->wherePivot('status', 1);
    }

    public function acceptedFollowers()
    {
        return $this->followers()->wherePivot('status', 1);
    }

    public function guideTrips()
    {
        return $this->hasMany(GuideTrip::class, 'guide_id');
    }

    public function guideTripUsers()
    {
        return $this->hasMany(GuideTripUser::class);
    }

    // Define a relationship with the GuideTrip model through the GuideTripUser model
    public function TripsOfGuide()
    {
        return $this->hasManyThrough(GuideTrip::class, GuideTripUser::class, 'user_id', 'id', 'id', 'guide_trip_id');
    }

    public function guideRatings()
    {
        return $this->hasMany(RatingGuide::class,'guide_id');
    }

    public function averageRating()
    {
        return $this->guideRatings()->avg('rating');
    }

    public function userGuideRating()
    {
        return $this->hasMany(RatingGuide::class,'user_id');
    }

    public function reviews()
    {
        return $this->hasMany(Reviewable::class);
    }

    public function conversations() : HasMany
    {
        return $this->hasMany(Conversation::class);
    }
}

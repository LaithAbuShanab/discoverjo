<?php

namespace App\Models;

use App\Notifications\Users\UserEmailVerificationNotification;
use App\Notifications\Users\UserResetPasswordNotification;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use LevelUp\Experience\Concerns\GiveExperience;
use LevelUp\Experience\Concerns\HasStreaks;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Permission\Traits\HasRoles;
use Laravel\Passport\HasApiTokens;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;
use Spatie\Translatable\HasTranslations;
use Illuminate\Support\Str;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasName;

class User extends Authenticatable implements MustVerifyEmail, HasMedia, FilamentUser, HasName
{
    use \Spatie\MediaLibrary\InteractsWithMedia;
    use HasApiTokens, HasFactory, Notifiable, HasRoles, HasTranslations, HasSlug, LogsActivity, GiveExperience, HasStreaks;
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
        'type',
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

    protected static $logAttributes = ['first_name', 'last_name', 'username', 'birthday', 'sex', 'email', 'description', 'phone_number', 'longitude', 'latitude', 'status'];
    protected static $logOnlyDirty = true;
    protected static $logName = 'user';
    protected static $recordEvents = ['created', 'updated', 'deleted'];

    public function getDescriptionForEvent(string $eventName): string
    {
        return "A user has been {$eventName}";
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('user')
            ->logOnly(self::$logAttributes)
            ->logOnlyDirty();
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('username')
            ->saveSlugsTo('slug')
            ->doNotGenerateSlugsOnUpdate(); // This prevents slug regeneration on updates
    }

    public function registerMediaCollections(): void
    {
        // Avatar collection (Profile Image)
        $this->addMediaCollection('avatar')
            ->singleFile()
            ->registerMediaConversions(function (Media $media) {
                $this->addMediaConversion('avatar_app')
                    ->format('webp')
                    ->nonQueued();
            });

        // File collection (Supports images & PDFs)
        $this->addMediaCollection('file')
            ->singleFile()
            ->acceptsMimeTypes([
                'image/jpeg',
                'image/png',
                'image/bmp',
                'image/gif',
                'image/svg+xml',
                'image/webp',
                'application/pdf'
            ])
            ->registerMediaConversions(function (Media $media) {
                // Convert only image files (not PDFs)
                if (str_starts_with($media->mime_type, 'image/')) {
                    $this->addMediaConversion('file_preview')
                        ->format('webp')
                        ->nonQueued();
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
        return $this->morphedByMany(Place::class, 'favorable')->withTimestamps();
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

    public function favoriteEvents()
    {
        return $this->morphedByMany(Event::class, 'favorable')->withTimestamps();
    }

    public function favoriteVolunteerings()
    {
        return $this->morphedByMany(Volunteering::class, 'favorable')->withTimestamps();
    }

    public function favoriteTrips()
    {
        return $this->morphedByMany(Trip::class, 'favorable')->withTimestamps();
    }

    public function favoriteGuideTrips()
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
    public function reviewService()
    {
        return $this->morphedByMany(Service::class, 'reviewable')->withTimestamps();
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

    public function DeviceTokenMany(): HasMany
    {
        return $this->hasMany(DeviceToken::class, 'user_id');
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
        return $this->following()
            ->wherePivot('status', 1)
            ->where('users.status', 1);
    }

    public function acceptedFollowers()
    {
        return $this->followers()
            ->wherePivot('status', 1)
            ->where('users.status', 1);
    }

    public function requestFollowers()
    {
        return $this->hasMany(Follow::class, 'following_id')
            ->where('status', 0)
            ->whereHas('followerUser', function ($query) {
                $query->where('status', 1);
            });
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
        return $this->hasMany(RatingGuide::class, 'guide_id');
    }

    public function averageRating()
    {
        return $this->guideRatings()->avg('rating');
    }

    public function userGuideRating()
    {
        return $this->hasMany(RatingGuide::class, 'user_id');
    }

    public function reviews()
    {
        return $this->hasMany(Reviewable::class)->latest();
    }

    public function conversations(): HasMany
    {
        return $this->hasMany(Conversation::class);
    }

    public function isFollowing(User $user): bool
    {
        return $this->following()->where('following_id', $user->id)->exists();
    }

    // Referrals this user made
    public function referralsMade()
    {
        return $this->hasMany(Referral::class, 'referrer_id');
    }

    // Referral this user received
    public function referralReceived()
    {
        return $this->hasOne(Referral::class, 'referred_id');
    }

    protected static function booted(): void
    {
        // static::created(function (self $user) {
        //     $prefix = substr(Str::slug($user->username), 0, 4);

        //     do {
        //         $code = strtoupper($prefix . rand(1000, 9999));
        //     } while (self::where('referral_code', $code)->exists());

        //     $user->forceFill(['referral_code' => $code])->saveQuietly();
        // });

        static::updating(function (self $model) {
            $watchedFields = [
                'first_name',
                'last_name',
                'username',
                'birthday',
                'sex',
                'email',
                'description',
                'phone_number',
                'longitude',
                'latitude',
                'status',
            ];

            $dirty = collect($model->getDirty())->only($watchedFields);

            if ($dirty->isEmpty()) {
                return false; // إلغاء عملية update بالكامل
            }
        });
    }
    public function getFilamentName(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }
    public function canAccessPanel(\Filament\Panel $panel): bool
    {
        return true;
    }
    public function reportsMade()
    {
        return $this->hasMany(Warning::class, 'reporter_id');
    }

    public function reportsReceived()
    {
        return $this->hasMany(Warning::class, 'reported_id');
    }

    public function favoriteServices()
    {
        return $this->morphedByMany(Service::class, 'favorable')->withTimestamps();
    }

    public function services()
    {
        return $this->morphMany(Service::class, 'provider');
    }

    public function reviewProperty()
    {
        return $this->morphedByMany(Property::class, 'reviewable')->withTimestamps();
    }

    public function favoritePropertys()
    {
        return $this->morphedByMany(Property::class, 'favorable')->withTimestamps();
    }

    public function userTypes()
    {
        return $this->hasMany(UserType::class, 'user_id');
    }
}

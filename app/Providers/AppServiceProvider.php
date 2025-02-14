<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(\App\Models\Admin::class, \App\Policies\AdminPolicy::class);
        Gate::policy(\App\Models\Category::class, \App\Policies\CategoryPolicy::class);
        Gate::policy(\App\Models\Organizer::class, \App\Policies\OrganizerPolicy::class);
        Gate::policy(\App\Models\Volunteering::class, \App\Policies\VolunteeingPolicy::class);
        Gate::policy(\App\Models\Slider::class, \App\Policies\SliderPolicy::class);
        Gate::policy(\App\Models\Trip::class, \App\Policies\TripPolicy::class);
        Gate::policy(\App\Models\User::class, \App\Policies\UserPolicy::class);
        Gate::policy(\App\Models\ContactUs::class, \App\Policies\ContactUsPolicy::class);
        Gate::policy(\App\Models\SuggestionPlace::class, \App\Policies\SuggestionPlacePolicy::class);
        Gate::policy(\App\Models\Post::class, \App\Policies\PostPolicy::class);
        Gate::policy(\App\Models\Comment::class, \App\Policies\CommentPolicy::class);
    }
}

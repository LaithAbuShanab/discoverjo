<?php

namespace App\Providers;

use App\Interfaces\Gateways\Api\User\AuthApiRepositoryInterface;
use App\Interfaces\Gateways\Api\User\CategoryApiRepositoryInterface;
use App\Interfaces\Gateways\Api\User\CommentApiRepositoryInterface;
use App\Interfaces\Gateways\Api\User\ContactUsApiRepositoryInterface;
use App\Interfaces\Gateways\Api\User\EventApiRepositoryInterface;
use App\Interfaces\Gateways\Api\User\FeaturesApiRepositoryInterface;
use App\Interfaces\Gateways\Api\User\FollowApiRepositoryInterface;
use App\Interfaces\Gateways\Api\User\GameApiRepositoryInterface;
use App\Interfaces\Gateways\Api\User\GroupChatRepositoryInterface;
use App\Interfaces\Gateways\Api\User\GuideRatingApiRepositoryInterface;
use App\Interfaces\Gateways\Api\User\GuideTripApiRepositoryInterface;
use App\Interfaces\Gateways\Api\User\GuideTripUserApiRepositoryInterface;
use App\Interfaces\Gateways\Api\User\LegalDocumentApiRepositoryInterface;
use App\Interfaces\Gateways\Api\User\PlaceApiRepositoryInterface;
use App\Interfaces\Gateways\Api\User\PlanApiRepositoryInterface;
use App\Interfaces\Gateways\Api\User\PopularPlaceApiRepositoryInterface;
use App\Interfaces\Gateways\Api\User\PostApiRepositoryInterface;
use App\Interfaces\Gateways\Api\User\RegionsApiRepositoryInterface;
use App\Interfaces\Gateways\Api\User\RegisterGuideApiRepositoryInterface;
use App\Interfaces\Gateways\Api\User\ReplyApiRepositoryInterface;
use App\Interfaces\Gateways\Api\User\SliderApiRepositoryInterface;
use App\Interfaces\Gateways\Api\User\SubCategoryApiRepositoryInterface;
use App\Interfaces\Gateways\Api\User\SuggestionPlaceApiRepositoryInterface;
use App\Interfaces\Gateways\Api\User\TopTenPlaceApiRepositoryInterface;
use App\Interfaces\Gateways\Api\User\TripApiRepositoryInterface;
use App\Interfaces\Gateways\Api\User\UserProfileApiRepositoryInterface;
use App\Interfaces\Gateways\Api\User\VolunteeringApiRepositoryInterface;
use App\Interfaces\Gateways\Web\Admin\AdminRepositoryInterface;
use App\Interfaces\Gateways\Web\Admin\CategoryRepositoryInterface;
use App\Interfaces\Gateways\Web\Admin\ContactUsRepositoryInterface;
use App\Interfaces\Gateways\Web\Admin\EventRepositoryInterface;
use App\Interfaces\Gateways\Web\Admin\FeatureRepositoryInterface;
use App\Interfaces\Gateways\Web\Admin\GuideRepositoryInterface;
use App\Interfaces\Gateways\Web\Admin\LegalDocumentRepositoryInterface;
use App\Interfaces\Gateways\Web\Admin\NotificationRepositoryInterface;
use App\Interfaces\Gateways\Web\Admin\OrganizerRepositoryInterface;
use App\Interfaces\Gateways\Web\Admin\PermissionRepositoryInterface;
use App\Interfaces\Gateways\Web\Admin\PlaceRepositoryInterface;
use App\Interfaces\Gateways\Web\Admin\PlanRepositoryInterface;
use App\Interfaces\Gateways\Web\Admin\PopularPlaceRepositoryInterface;
use App\Interfaces\Gateways\Web\Admin\PostRepositoryInterface;
use App\Interfaces\Gateways\Web\Admin\QuestionChainRepositoryInterface;
use App\Interfaces\Gateways\Web\Admin\QuestionRepositoryInterface;
use App\Interfaces\Gateways\Web\Admin\RegionRepositoryInterface;
use App\Interfaces\Gateways\Web\Admin\RoleRepositoryInterface;
use App\Interfaces\Gateways\Web\Admin\SliderRepositoryInterface;
use App\Interfaces\Gateways\Web\Admin\SubCategoryRepositoryInterface;
use App\Interfaces\Gateways\Web\Admin\SuggestionPlaceRepositoryInterface;
use App\Interfaces\Gateways\Web\Admin\TagRepositoryInterface;
use App\Interfaces\Gateways\Web\Admin\TopTenPlaceRepositoryInterface;
use App\Interfaces\Gateways\Web\Admin\TripRepositoryInterface;
use App\Interfaces\Gateways\Web\Admin\UserRepositoryInterface;
use App\Interfaces\Gateways\Web\Admin\VolunteeringRepositoryInterface;
use App\Interfaces\Gateways\Web\Setting\LanguageRepositoryInterface;
use App\Repositories\Api\User\EloquentAuthApiRepository;
use App\Repositories\Api\User\EloquentCategoryApiRepository;
use App\Repositories\Api\User\EloquentCommentApiRepository;
use App\Repositories\Api\User\EloquentContactUsApiRepository;
use App\Repositories\Api\User\EloquentEventApiRepository;
use App\Repositories\Api\User\EloquentFeaturesApiRepository;
use App\Repositories\Api\User\EloquentFollowApiRepository;
use App\Repositories\Api\User\EloquentGameApiRepository;
use App\Repositories\Api\User\EloquentGroupChatRepository;
use App\Repositories\Api\User\EloquentGuideRatingApiApiRepository;
use App\Repositories\Api\User\EloquentGuideTripApiRepository;
use App\Repositories\Api\User\EloquentGuideTripUserApiRepository;
use App\Repositories\Api\User\EloquentLegalDocumentApiRepository;
use App\Repositories\Api\User\EloquentPlaceApiRepository;
use App\Repositories\Api\User\EloquentPlanApiRepository;
use App\Repositories\Api\User\EloquentPopularPlaceApiRepository;
use App\Repositories\Api\User\EloquentPostApiRepository;
use App\Repositories\Api\User\EloquentRegionsApiRepository;
use App\Repositories\Api\User\EloquentRegisterGuideApiRepository;
use App\Repositories\Api\User\EloquentReplyApiRepository;
use App\Repositories\Api\User\EloquentSliderApiRepository;
use App\Repositories\Api\User\EloquentSubCategoryApiRepository;
use App\Repositories\Api\User\EloquentSuggestionPlaceApiRepository;
use App\Repositories\Api\User\EloquentTopTenPlaceApiRepository;
use App\Repositories\Api\User\EloquentTripApiRepository;
use App\Repositories\Api\User\EloquentUserProfileApiRepository;
use App\Repositories\Api\User\EloquentVolunteeringApiRepository;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {


        $this->app->bind(CategoryApiRepositoryInterface::class, EloquentCategoryApiRepository::class);

        $this->app->bind(PlaceApiRepositoryInterface::class, EloquentPlaceApiRepository::class);

        $this->app->bind(SubCategoryApiRepositoryInterface::class, EloquentSubCategoryApiRepository::class);

        $this->app->bind(TopTenPlaceApiRepositoryInterface::class, EloquentTopTenPlaceApiRepository::class);

        $this->app->bind(PopularPlaceApiRepositoryInterface::class, EloquentPopularPlaceApiRepository::class);

        $this->app->bind(EventApiRepositoryInterface::class, EloquentEventApiRepository::class);


        $this->app->bind(VolunteeringApiRepositoryInterface::class, EloquentVolunteeringApiRepository::class);


        $this->app->bind(PlanApiRepositoryInterface::class, EloquentPlanApiRepository::class);

        $this->app->bind(AuthApiRepositoryInterface::class, EloquentAuthApiRepository::class);

        $this->app->bind(UserProfileApiRepositoryInterface::class, EloquentUserProfileApiRepository::class);

        $this->app->bind(TripApiRepositoryInterface::class, EloquentTripApiRepository::class);

        $this->app->bind(PostApiRepositoryInterface::class, EloquentPostApiRepository::class);

        $this->app->bind(LegalDocumentApiRepositoryInterface::class, EloquentLegalDocumentApiRepository::class);

        $this->app->bind(ContactUsApiRepositoryInterface::class, EloquentContactUsApiRepository::class);

        $this->app->bind(FollowApiRepositoryInterface::class, EloquentFollowApiRepository::class);

        $this->app->bind(CommentApiRepositoryInterface::class, EloquentCommentApiRepository::class);

        $this->app->bind(ReplyApiRepositoryInterface::class, EloquentReplyApiRepository::class);

        $this->app->bind(SuggestionPlaceApiRepositoryInterface::class, EloquentSuggestionPlaceApiRepository::class);

        $this->app->bind(SliderApiRepositoryInterface::class, EloquentSliderApiRepository::class);

        $this->app->bind(GameApiRepositoryInterface::class, EloquentGameApiRepository::class);

        $this->app->bind(GuideTripApiRepositoryInterface::class, EloquentGuideTripApiRepository::class);

        $this->app->bind(GuideTripUserApiRepositoryInterface::class, EloquentGuideTripUserApiRepository::class);

        $this->app->bind(RegisterGuideApiRepositoryInterface::class, EloquentRegisterGuideApiRepository::class);

        $this->app->bind(GuideRatingApiRepositoryInterface::class, EloquentGuideRatingApiApiRepository::class);

        $this->app->bind(RegionsApiRepositoryInterface::class, EloquentRegionsApiRepository::class);

        $this->app->bind(FeaturesApiRepositoryInterface::class, EloquentFeaturesApiRepository::class);

        $this->app->bind(GroupChatRepositoryInterface::class, EloquentGroupChatRepository::class);
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
        Gate::policy(\App\Models\LegalDocument::class, \App\Policies\LegalDocumentPolicy::class);
    }
}

<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\User\CategoryApiController;
use App\Http\Controllers\Api\User\PlaceApiController;
use App\Http\Controllers\Api\User\SubCategoryApiController;
use App\Http\Controllers\Api\User\TopTenPlaceApiController;
use App\Http\Controllers\Api\User\PopularPlaceApiController;
use App\Http\Controllers\Api\User\EventApiController;
use App\Http\Controllers\Api\User\VolunteeringApiController;
use App\Http\Controllers\Api\User\LegalDocumentApiController;
use App\Http\Controllers\Api\User\ContactUsApiController;
use App\Http\Controllers\Api\User\FeaturesApiController;
use App\Http\Controllers\Api\User\TripApiController;
use App\Http\Controllers\Api\User\PlanApiController;
use App\Http\Controllers\Api\User\UserProfileController;
use App\Http\Controllers\Api\User\SuggestionPlaceApiController;
use App\Http\Controllers\Api\User\SliderApiController;
use App\Http\Controllers\Api\User\GuideTripUserApiController;
use App\Http\Controllers\Api\User\GuideTripApiController;
use App\Http\Controllers\Api\User\RegionsApiController;
use App\Http\Controllers\Api\User\RegisterGuide\RegisterGuideApiController;


//start review and fix
Route::get('all-categories', [CategoryApiController::class, 'index'])->name('categories');
Route::get('list/subcategories', [CategoryApiController::class, 'subcategoriesOfCategories'])->name('subcategories.Categories');
Route::get('shuffle/all-categories', [CategoryApiController::class, 'shuffleAllCategories'])->name('categories.shuffle');
Route::get('places/category/{category_slug}', [CategoryApiController::class, 'categoryPlaces'])->name('category.places');

Route::get('place/{place_slug}', [PlaceApiController::class, 'singlePlaces'])->name('place');
Route::get('places/subcategory/{subcategory_slug}', [SubCategoryApiController::class, 'singleSubCategory'])->name('subcategories.places');

Route::get('top-ten-places', [TopTenPlaceApiController::class, 'topTenPlaces'])->name('topTen.places');
Route::get('popular/places', [PopularPlaceApiController::class, 'popularPlaces'])->name('popular.places');

//////////////////////////////////////// event api //////////////////////////////////////////////////////////////////
// this api for all event active and inactive order by start_time old to new
Route::get('all/events', [EventApiController::class, 'index'])->name('events');
Route::get('all/active/events', [EventApiController::class, 'activeEvents'])->name('active.events');
Route::get('event/{event_slug}', [EventApiController::class, 'event'])->name('single.events');
Route::get('date/events', [EventApiController::class, 'dateEvents'])->name('date.events');

///////////////////////////////////volunteering api /////////////////////////////////////////////////////
Route::get('all/volunteering', [VolunteeringApiController::class, 'index'])->name('volunteering');
Route::get('all/active/volunteering', [VolunteeringApiController::class, 'activeVolunteerings'])->name('active.volunteering');
Route::get('volunteering/{volunteering_slug}', [VolunteeringApiController::class, 'volunteering'])->name('single.volunteering');
Route::get('date/volunteering', [VolunteeringApiController::class, 'dateVolunteering'])->name('date.volunteering');

Route::get('legal/document', [LegalDocumentApiController::class, 'index'])->name('legal.index');
Route::post('contact-us', [ContactUsApiController::class, 'store'])->name('contact.store');
Route::post('suggestion/places', [SuggestionPlaceApiController::class, 'store']);

Route::get('all/trips', [TripApiController::class, 'allTrip'])->name('trips');

//////////////////////////////////search and filter /////////////////////////////////////////
Route::get('all/places/search', [PlaceApiController::class, 'search']);
Route::get('popular/places/search', [PopularPlaceApiController::class, 'search']);
Route::get('top-ten/places/search', [TopTenPlaceApiController::class, 'search']);
Route::get('categories/search', [CategoryApiController::class, 'search']);
Route::get('all/event/search', [EventApiController::class, 'search']);
Route::get('all/volunteering/search', [VolunteeringApiController::class, 'search']);
Route::get('all/trip/search', [TripApiController::class, 'search']);
Route::get('all/guide-trip/search', [GuideTripUserApiController::class, 'search']);
Route::get('all/plan/search', [PlanApiController::class, 'search']);
Route::get('user/search', [UserProfileController::class, 'search']);
Route::get('all/search', [PlaceApiController::class, 'allSearch']);
Route::get('places/filter', [PlaceApiController::class, 'filter']);
Route::get('user/current-location/places', [UserProfileController::class, 'currentLocation']);

Route::get('/onboarding/images', [SliderApiController::class, 'onboardings']);

///////////////////////////////////Guide trips ///////////////////////////////////////////
Route::get('user/guide/trips', [GuideTripUserApiController::class, 'index']);
Route::get('user/guide/trips/show/{guide_trip_slug}', [GuideTripApiController::class, 'show']);
Route::get('all/guides', [GuideTripApiController::class, 'allGuides']);
Route::post('guide/register', [RegisterGuideApiController::class, 'register']);
Route::get('/guides/trips', [GuideTripApiController::class, 'index']);

Route::get('all/regions', [RegionsApiController::class, 'index']);
Route::get('all/features', [FeaturesApiController::class, 'index']);


//end review


//here



Route::get('all/plans', [PlanApiController::class, 'allPlans'])->name('plans');
Route::get('plan/filter', [PlanApiController::class, 'filter']);

require __DIR__ . '/auth_user.php';

Route::fallback(function () {
    return response()->json(['msg' => 'this url not exists in this project walaa 7abibi fix the url :) ']);
});

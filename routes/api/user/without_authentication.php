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

// GET ALL CATEGORIES
Route::get('all-categories', [CategoryApiController::class, 'index'])->name('categories'); // DONE ✅
// GET ALL SUBCATEGORIES
Route::get('list/subcategories', [CategoryApiController::class, 'subcategoriesOfCategories'])->name('subcategories.categories'); // DONE ✅
// GET ALL CATEGORIES SHUFFLE
Route::get('shuffle/all-categories', [CategoryApiController::class, 'shuffleAllCategories'])->name('categories.shuffle'); // DONE ✅
// GET ALL PLACES BY CATEGORY
Route::get('places/category/{category_slug}', [CategoryApiController::class, 'categoryPlaces'])->name('category.places'); // DONE ✅
// GET ALL PLACES BY SLUG
Route::get('place/{place_slug}', [PlaceApiController::class, 'singlePlaces'])->name('place'); // DONE ✅
// GET ALL PLACES BY SUBCATEGORY
Route::get('places/subcategory/{subcategory_slug}', [SubCategoryApiController::class, 'singleSubCategory'])->name('subcategories.places'); // DONE ✅
// GET TOP PLACES
Route::get('top-ten-places', [TopTenPlaceApiController::class, 'topTenPlaces'])->name('topTen.places'); // DONE ✅
// GET POPULAR PLACES
Route::get('popular/places', [PopularPlaceApiController::class, 'popularPlaces'])->name('popular.places'); // DONE ✅
// GET ALL EVENTS
Route::get('all/events', [EventApiController::class, 'index'])->name('events'); // DONE ✅
// GET ALL ACTIVE EVENTS
Route::get('all/active/events', [EventApiController::class, 'activeEvents'])->name('active.events'); // DONE ✅
// GET SINGLE EVENT
Route::get('event/{event_slug}', [EventApiController::class, 'event'])->name('single.events'); // DONE ✅
// GET DATE EVENTS
Route::get('date/events', [EventApiController::class, 'dateEvents'])->name('date.events'); // DONE ✅
// GET ALL VOLUNTEERINGS
Route::get('all/volunteering', [VolunteeringApiController::class, 'index'])->name('volunteering'); // DONE ✅
// GET ALL ACTIVE VOLUNTEERINGS
Route::get('all/active/volunteering', [VolunteeringApiController::class, 'activeVolunteerings'])->name('active.volunteering'); // DONE ✅
// GET SINGLE VOLUNTEERING
Route::get('volunteering/{volunteering_slug}', [VolunteeringApiController::class, 'volunteering'])->name('single.volunteering'); // DONE ✅
// GET DATE VOLUNTEERING
Route::get('date/volunteering', [VolunteeringApiController::class, 'dateVolunteering'])->name('date.volunteering'); // DONE ✅
// GET ALL LEGAL DOCUMENT
Route::get('legal/document', [LegalDocumentApiController::class, 'index'])->name('legal.index'); // DONE ✅
// POST CONTACT US
Route::post('contact-us', [ContactUsApiController::class, 'store'])->name('contact.store'); // DONE ✅
// POST SUGGESTION
Route::post('suggestion/places', [SuggestionPlaceApiController::class, 'store']); // DONE ✅
// GET ALL TRIPS
Route::get('all/trips', [TripApiController::class, 'allTrip'])->name('trips'); // DONE ✅
// GET SEARCHABLE PLACES
Route::get('all/places/search', [PlaceApiController::class, 'search']); // DONE ✅
// GET SEARCHABLE POPULAR PLACES
Route::get('popular/places/search', [PopularPlaceApiController::class, 'search']); // DONE ✅
// GET SEARCHABLE TOP TEN PLACES
Route::get('top-ten/places/search', [TopTenPlaceApiController::class, 'search']); // DONE ✅
// GET SEARCHABLE CATEGORIES
Route::get('categories/search', [CategoryApiController::class, 'search']); // DONE ✅
// GET SEARCHABLE EVENT
Route::get('all/event/search', [EventApiController::class, 'search']); // DONE ✅
// GET SEARCHABLE VOLUNTEERING
Route::get('all/volunteering/search', [VolunteeringApiController::class, 'search']); // DONE ✅
// GET SEARCHABLE TRIPS
Route::get('all/trip/search', [TripApiController::class, 'search']); // DONE ✅
// GET SEARCHABLE GUIDES
Route::get('all/guide-trip/search', [GuideTripUserApiController::class, 'search']); // DONE ✅
// GET SEARCHABLE PLANS
Route::get('all/plan/search', [PlanApiController::class, 'search']); // DONE ✅
// GET SEARCHABLE USERS
Route::get('user/search', [UserProfileController::class, 'search']); // DONE ✅
// GET ALL SEARCHABLE
Route::get('all/search', [PlaceApiController::class, 'allSearch']); // DONE ✅
// GET FILTERED PLACES
Route::get('places/filter', [PlaceApiController::class, 'filter']); // DONE ✅
// GET USER CURRENT LOCATION
Route::get('user/current-location/places', [UserProfileController::class, 'currentLocation']); // DONE ✅
// GET ALL SLIDER
Route::get('/onboarding/images', [SliderApiController::class, 'onboardings']); // DONE ✅
// GET ALL USER GUIDES TRIPS
Route::get('user/guide/trips', [GuideTripUserApiController::class, 'index']); // DONE ✅
// GET SINGLE GUIDE TRIP
Route::get('user/guide/trips/show/{slug}', [GuideTripApiController::class, 'show']); // DONE ✅
// GET ALL GUIDE USERS
Route::get('all/guides', [GuideTripApiController::class, 'allGuides']); // DONE ✅
// POST GUIDE REGISTER
Route::post('guide/register', [RegisterGuideApiController::class, 'register']); // DONE ✅
// GET GUIDE TRIPS
Route::get('/guides/trips', [GuideTripApiController::class, 'index']); // DONE ✅
// GET ALL REGIONS
Route::get('all/regions', [RegionsApiController::class, 'index']); // DONE ✅
// GET ALL FEATURES
Route::get('all/features', [FeaturesApiController::class, 'index']); // DONE ✅
// GET ALL PLANS
Route::get('all/plans', [PlanApiController::class, 'allPlans'])->name('plans'); // DONE ✅
// GET FILTERED PLANS
Route::get('plan/filter', [PlanApiController::class, 'filter']); // DONE ✅

require __DIR__ . '/auth_user.php';

Route::fallback(function () {
    return response()->json(['msg' => 'this url not exists in this project walaa 7abibi fix the url :) ']);
});

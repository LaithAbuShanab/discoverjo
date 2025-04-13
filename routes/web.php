<?php

use Illuminate\Support\Facades\Route;


use App\Models\Place;
use Spatie\Sluggable\SlugOptions;
use Illuminate\Support\Str;
Route::get('generate-slug', function () {
    // Get all places that do not have a slug
    $guideTripsWithoutSlug = \App\Models\Plan::whereNull('slug')->orWhere('slug', '')->get();

    foreach ($guideTripsWithoutSlug as $guideTrip) {
        $guideTrip->slug = Str::slug($guideTrip->name);
        $guideTrip->save();
    }

    return response()->json([
        'message' => 'Slugs generated successfully!',
        'count' => $guideTripsWithoutSlug->count(),
    ]);
});

Route::get('/check-time', function () {
    return 'Laravel time: ' . now()->toDateTimeString();
});

Route::get('generate-places', [\App\Http\Controllers\AutomaticPlaceController::class,'insertPlacesFromJson']);



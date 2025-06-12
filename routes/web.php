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

// routes/web.php
Route::get('/test-email', function () {
    \Illuminate\Support\Facades\Mail::raw('Test email from Laravel.', function ($message) {
        $message->to('asma.abughaith@gmail.com')
            ->subject('Test Email');
    });
    return 'Email Sent!';
});


Route::get('generate-places', [\App\Http\Controllers\AutomaticPlaceController::class,'insertPlacesFromJson']);



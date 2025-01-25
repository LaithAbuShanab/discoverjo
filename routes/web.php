<?php

use Illuminate\Support\Facades\Route;


use App\Models\Place;
use Spatie\Sluggable\SlugOptions;
use Illuminate\Support\Str;
Route::get('generate-slug', function () {
    // Get all places that do not have a slug
    $placesWithoutSlug = \App\Models\Event::whereNull('slug')->orWhere('slug', '')->get();

    foreach ($placesWithoutSlug as $place) {
        $place->slug = Str::slug($place->name);
        $place->save();
    }

    return response()->json([
        'message' => 'Slugs generated successfully!',
        'count' => $placesWithoutSlug->count(),
    ]);
});


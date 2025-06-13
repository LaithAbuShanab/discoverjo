<?php

use App\Http\Resources\LegalResource;
use App\Models\LegalDocument;
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

Route::get('/privacy-policy', function () {
    $legalDocuments = LegalDocument::with('terms')->get();
    $groupedDocuments = $legalDocuments->groupBy('type');

    $formattedData = [];
    foreach ($groupedDocuments as $type => $documents) {
        $typeName = $type == 1 ? 'Privacy And Policy' : 'Terms Of Service';
        $formattedData[] = [
            $typeName => LegalResource::collection($documents)
        ];
    }

    $lastLegalDate = LegalDocument::latest('updated_at')->first()?->updated_at?->toDateString();

    return view('privacy', [
        'last_updated' => $lastLegalDate,
        'data' => $formattedData
    ]);
});


Route::get('generate-places', [\App\Http\Controllers\AutomaticPlaceController::class,'insertPlacesFromJson']);



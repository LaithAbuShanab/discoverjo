<?php

namespace App\Http\Controllers;

use App\Models\Feature;
use App\Models\Place;
use App\Models\Region;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AutomaticPlaceController extends Controller
{
    public function insertPlacesFromJson()
    {
        $jsonEn = File::get(storage_path('app/tourist_attraction-en.json'));
        $jsonAr = File::get(storage_path('app/tourist_attraction-ar.json'));
        $placesEn = json_decode($jsonEn, true);
        $placesAr = json_decode($jsonAr, true);

        $businessStatusMapping = [
            'closed' => 0,
            'operational' => 1,
            'temporary_closed' => 2,
            'i do not know' => 3,
        ];

        foreach ($placesEn as $key => $placeData) {
            if (
                !Place::where('google_place_id', $placeData['place_id'])->exists() &&
                !DB::table('trash_places')->where('google_place_id', $placeData['place_id'])->exists()
            ) {

                // Ensure both translations exist, defaulting to empty string if missing
                $translatorName = [
                    'en' => $placeData['name'] ?? 'there is no data here',
                    'ar' => $placesAr[$key]['name'] ?? 'ليس هناك معلومة باللغة العربية متوفر'
                ];
                $translatorDescription = [
                    'en' => $placeData['description'] ?? 'there is no data here',
                    'ar' => $placesAr[$key]['description'] ?? 'ليس هناك معلومة باللغة العربية متوفر'
                ];

                // Ensure the region exists
                $queryPartsEn = explode(',', $placeData['query']);
                $cityNameFromQueryEn = (isset($queryPartsEn) && count($queryPartsEn) >= 2)
                    ? trim($queryPartsEn[count($queryPartsEn) - 2])
                    : '';
                $addressNameFromQueryEn = $placeData['full_address'] ;

                $queryPartsAr = explode('،', $placesAr[$key]['query']);
                $cityNameFromQueryAr = (isset($queryPartsAr) && count($queryPartsAr) >= 2)
                    ? trim($queryPartsAr[count($queryPartsAr) - 2])
                    : '';
                $addressNameFromQueryAr = $placesAr[$key]['full_address'] ;




                $translatorAddress = [
                    'en' => $addressNameFromQueryEn ?$addressNameFromQueryEn: 'there is no data here',
                    'ar' => $addressNameFromQueryAr ?$addressNameFromQueryAr: 'ليس هناك معلومة باللغة العربية متوفر'
                ];


                $region = Region::firstOrCreate(
                    ['name->en' => $cityNameFromQueryEn],
                    ['name' => [
                        'en' => $cityNameFromQueryEn ?? 'there is no data here',
                        'ar' => $cityNameFromQueryAr ?? 'ليس هناك معلومة باللغة العربية متوفر',
                    ]]
                );
                $region->setTranslations('name', ['en' => $cityNameFromQueryEn ?? 'there is no data here', 'ar' => $cityNameFromQueryAr ?? 'there is no data here']);

                // Create and save the place
                $place = new Place();
                $place->name = $translatorName;
                $place->description = $translatorDescription;
                $place->address = $translatorAddress;
                $place->longitude = $placeData['longitude'] ?? 10.000;
                $place->latitude = $placeData['latitude'] ?? 10.000;
                $place->phone_number = $placeData['phone'] ?? null;
                $place->business_status = $businessStatusMapping[strtolower($placeData['business_status'])] ?? 3;
                $place->price_level = $placeData['range'] ? strlen($placeData['range']) : -1;
                $place->website = $placeData['site'] ?? null;
                $place->rating = $placeData['rating'];
                $place->total_user_rating = $placeData['reviews'];
                $place->google_map_url = $placeData['location_link'] ?? 'http:asma.com';
                $place->region_id = $region->id;
                $place->status = 0;
                $place->google_place_id = $placeData['place_id'];
                $place->save();

                $place->categories()->attach([8]);
                $place->tags()->attach([3,5]);
                $place->setTranslations('name', $translatorName);
                $place->setTranslations('description', $translatorDescription);
                $place->setTranslations('address', $translatorAddress);

                // Insert features
                $this->insertFeatures($place, $placeData['about']);

                // Insert opening hours
                if ($placeData['working_hours']) {
                    $this->insertOpeningHours($place, $placeData['working_hours']);
                }

                // Save image to media library
                if (isset($placeData['photo'])) {
                    try {
                        $this->saveImageToMediaLibrary($place, $placeData['photo']);
                    } catch (\Exception $e) {
                        // Log the error but continue processing
                        Log::error("Failed to save image for place {$placeData['place_id']}. Error: " . $e->getMessage());
                    }
                }
            }
        }
    }

    private function saveImageToMediaLibrary($place, $imageUrl)
    {
        // Generate a unique filename
        $extension = pathinfo($imageUrl, PATHINFO_EXTENSION);
        $filename = Str::random(10) . '_' . time() . '.' . $extension;

        try {
            // Attempt to add the media from the URL
            $place->addMediaFromUrl($imageUrl)
                ->usingFileName($filename)
                ->toMediaCollection('main_place');
        } catch (\Spatie\MediaLibrary\Exceptions\FileCannotBeAdded $e) {
            // Handle the exception when the URL is not reachable or other issues occur
            Log::error("Failed to add media from URL: $imageUrl. Error: " . $e->getMessage());
            throw $e; // Re-throw to be caught in the caller method
        } catch (\Exception $e) {
            // Handle any other exceptions
            Log::error("An unexpected error occurred while adding media from URL: $imageUrl. Error: " . $e->getMessage());
            throw $e; // Re-throw to be caught in the caller method
        }
    }

    private function insertFeatures($place, $about)
    {
        $features = [];

        if ($about) {
            foreach ($about as $category => $details) {
                foreach ($details as $feature => $value) {
                    if ($value === true) {
                        // Check if feature exists
                        $existingFeature = Feature::where('name->en', $feature)->first();

                        if (!$existingFeature) {

                            $existingFeature = new Feature();
                            $existingFeature->name = ['en' => $feature, 'ar' => $feature];
                            $existingFeature->save();
                            $existingFeature->setTranslations('name', ['en' => $feature, 'ar' => $feature]);
                        }

                        $features[] = $existingFeature->id;
                    }
                }
            }
        }

        // Attach features to place
        $place->features()->sync($features);
    }


    private function insertOpeningHours($place, $hours)
    {
        foreach ($hours as $day => $time) {
            if (strtolower($time) === 'open 24 hours') {
                // Handle "Open 24 hours" case
                $formattedOpeningTime = '00:00:00';
                $formattedClosingTime = '23:59:59';

                $place->openingHours()->create([
                    'day_of_week' => $day,
                    'opening_time' => $formattedOpeningTime,
                    'closing_time' => $formattedClosingTime,
                ]);
            } elseif (strpos($time, '-') !== false) {
                // Handle standard opening hours format
                list($openingTime, $closingTime) = explode('-', $time);

                $formattedOpeningTime = $this->formatTime(trim($openingTime));
                $formattedClosingTime = $this->formatTime(trim($closingTime));

                if ($formattedOpeningTime && $formattedClosingTime) {
                    $place->openingHours()->create([
                        'day_of_week' => $day,
                        'opening_time' => $formattedOpeningTime,
                        'closing_time' => $formattedClosingTime,
                    ]);
                } else {
                    Log::error("Failed to parse opening or closing time for day: $day, time: $time");
                }
            } else {
                Log::error("Invalid time format for day: $day, time: $time");
            }
        }
    }

    // Function to format time using Carbon

    private function formatTime($time)
    {
        // List of possible formats
        $formats = [
            'h A',      // 1 AM, 1 PM
            'g A',      // 1 AM, 1 PM (single digit hour)
            'h:i A',    // 1:00 AM, 1:00 PM
            'g:i A',    // 1:00 AM, 1:00 PM (single digit hour)
            'H:i',      // 01:00, 13:00
            'H:i:s',    // 01:00:00, 13:00:00
            'h:i:s A',  // 01:00:00 AM, 01:00:00 PM
        ];

        foreach ($formats as $format) {
            try {
                return Carbon::createFromFormat($format, trim($time))->format('H:i:s');
            } catch (\Exception $e) {
                // Continue to next format if parsing fails
                continue;
            }
        }

        // Log error if no format matches
        Log::error("Time format not recognized: $time");
        return null;
    }
}

<?php

namespace App\Repositories\Api\User;

use App\Http\Resources\AllChaletsResource;
use App\Http\Resources\SingleChaletResource;
use App\Interfaces\Gateways\Api\User\ContactUsApiRepositoryInterface;
use App\Interfaces\Gateways\Api\User\PropertyApiRepositoryInterface;
use App\Models\ContactUs;
use App\Models\Property;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use App\Models\Admin;
use App\Notifications\Admin\NewContactNotification;

class EloquentPropertyApiRepository implements PropertyApiRepositoryInterface
{
    public function getAllChalets()
    {
        $perPage = config('app.pagination_per_page');
        $now = now()->setTimezone('Asia/Riyadh')->toDateString(); // use toDateString for date-only comparison

        $chalets = Property::where('status', 1)
            ->whereHas('availabilities', function ($query) use ($now) {
                $query->whereNull('parent_id')
                    ->whereDate('availability_end_date', '>=', $now);
            })
            ->whereHas('host', function ($query) {
                $query->where('status', 1);
            })
            ->orderBy('created_at') // you can replace this with any other valid field
            ->paginate($perPage);


        $chaletsArray = $chalets->toArray();

        $pagination = [
            'next_page_url' => $chaletsArray['next_page_url'],
            'prev_page_url' => $chaletsArray['next_page_url'],
            'total' => $chaletsArray['total'],
        ];


        // Pass user coordinates to the PlaceResource collection
        return [
//            'chalets' => AllChaletsResource::collection($chalets),
            'chalets' => AllChaletsResource::collection(
                $chalets->reject(function ($chalet) {
                    $currentUser = Auth::guard('api')->user();
                    if (!$currentUser) return false;
                    return $currentUser->blockedUsers->contains('id', $chalet->host_id) ||
                        $currentUser->blockers->contains('id', $chalet->host_id);
                })
            ),
            'pagination' => $pagination
        ];
    }

    public function singleProperty($slug)
    {
        $property = Property::findBySlug($slug);
        return new SingleChaletResource($property);
    }

    public function search($query)
    {
        $perPage = config('app.pagination_per_page');
        $lowerQuery = strtolower($query);

        $property = Property::where(function ($q) use ($lowerQuery) {
            $q->whereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(name, '$.en'))) LIKE ?", ["%{$lowerQuery}%"])
                ->orWhereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(name, '$.ar'))) LIKE ?", ["%{$lowerQuery}%"]);
        })->paginate($perPage);

        $pagination = [
            'next_page_url' => $property->nextPageUrl(),
            'prev_page_url' => $property->previousPageUrl(),
            'total'         => $property->total(),
        ];

        if (!empty($query) && $property->isNotEmpty()) {
            activityLog('Searched for property', $property->first(), $query, 'search');
        }

        return [
            'properties' => AllChaletsResource::collection(
                $property->reject(function ($service) {
                    $currentUser = Auth::guard('api')->user();
                    if (!$currentUser) return false;
                    return $currentUser->blockedUsers->contains('id', $service->host_id) ||
                        $currentUser->blockers->contains('id', $service->host_id);
                })
            ),
            'pagination' => $pagination,
        ];
    }
}

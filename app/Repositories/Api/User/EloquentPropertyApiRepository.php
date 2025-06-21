<?php

namespace App\Repositories\Api\User;

use App\Http\Resources\AllChaletsResource;
use App\Http\Resources\SingleChaletResource;
use App\Interfaces\Gateways\Api\User\ContactUsApiRepositoryInterface;
use App\Interfaces\Gateways\Api\User\PropertyApiRepositoryInterface;
use App\Models\ContactUs;
use App\Models\Property;
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
            'chalets' => AllChaletsResource::collection($chalets),
            'pagination' => $pagination
        ];
    }

    public function singleProperty($slug)
    {
        $property = Property::findBySlug($slug);
        return new SingleChaletResource($property);
    }
}

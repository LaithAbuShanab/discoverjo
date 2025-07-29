<?php

namespace App\Repositories\Api\User;

use App\Http\Resources\AllServicesResource;
use App\Interfaces\Gateways\Api\User\ServiceApiRepositoryInterface;
use App\Models\Service;
use Illuminate\Support\Facades\Auth;

class EloquentServiceApiRepository implements ServiceApiRepositoryInterface
{
    public function allServices()
    {
        $perPage = config('app.pagination_per_page');
        $now = now()->setTimezone('Asia/Riyadh')->toDateString(); // use toDateString for date-only comparison

        $services = Service::where('status', 1)
            ->whereHas('serviceBookings', function ($query) use ($now) {
                $query->whereDate('available_end_date', '>=', $now);

            })
            ->whereHas('provider', function ($query) {
                $query->where('status', 1);
            })
            ->with('serviceBookings') // optional: eager load bookings if needed
            ->orderBy('created_at') // you can replace this with any other valid field
            ->paginate($perPage);

        $serviceArray = $services->toArray();

        $pagination = [
            'next_page_url' => $serviceArray['next_page_url'],
            'prev_page_url' => $serviceArray['next_page_url'],
            'total' => $serviceArray['total'],
        ];

        // Pass user coordinates to the PlaceResource collection
        return [
//            'services' => AllServicesResource::collection($services),
            'services' => AllServicesResource::collection(
                $services->reject(function ($service) {
                    $currentUser = Auth::guard('api')->user();
                    if (!$currentUser) return false;
                    return $currentUser->blockedUsers->contains('id', $service->provider_id) ||
                        $currentUser->blockers->contains('id', $service->provider_id);
                })
            ),
            'pagination' => $pagination
        ];
    }
}

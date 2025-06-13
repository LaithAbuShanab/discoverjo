<?php

namespace App\Repositories\Api\User;

use App\Http\Resources\AllGuideTripResource;
use App\Http\Resources\AllServicesResource;
use App\Http\Resources\GuideResource;
use App\Http\Resources\GuideTripResource;
use App\Http\Resources\GuideTripUpdateDetailResource;
use App\Http\Resources\GuideTripUserResource;
use App\Interfaces\Gateways\Api\User\GuideTripApiRepositoryInterface;
use App\Interfaces\Gateways\Api\User\ServiceApiRepositoryInterface;
use App\Models\GuideTrip;
use App\Models\GuideTripActivity;
use App\Models\GuideTripAssembly;
use App\Models\GuideTripPaymentMethod;
use App\Models\GuideTripPriceAge;
use App\Models\GuideTripPriceInclude;
use App\Models\GuideTripRequirement;
use App\Models\GuideTripTrail;
use App\Models\GuideTripUser;
use App\Models\Service;
use App\Models\User;
use App\Notifications\Users\guide\AcceptCancelNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Str;
use LevelUp\Experience\Models\Activity;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Symfony\Component\HttpKernel\Exception\HttpException;


class EloquentServiceApiRepository implements ServiceApiRepositoryInterface
{
    public function allServices()
    {
        $perPage = config('app.pagination_per_page');
        $now = now()->setTimezone('Asia/Riyadh')->toDateString(); // use toDateString for date-only comparison

        $services = Service::where('status', 1)
            ->whereHas('serviceBookings', function ($query) use ($now) {
                $query->whereDate('available_start_date', '<=', $now)
                    ->whereDate('available_end_date', '>=', $now);
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
            'services' => AllServicesResource::collection($services),
            'pagination' => $pagination
        ];
    }


}

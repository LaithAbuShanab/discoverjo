<?php

namespace App\Repositories\Api\User;

use App\Http\Resources\AllGuideTripResource;
use App\Http\Resources\EventResource;
use App\Http\Resources\FeaturesResource;
use App\Http\Resources\GuideFavoriteResource;
use App\Http\Resources\PlaceResource;
use App\Http\Resources\PlanResource;
use App\Http\Resources\TripResource;
use App\Http\Resources\UserFavoriteResource;
use App\Http\Resources\UserFavoriteSearchResource;
use App\Http\Resources\UserResource;
use App\Http\Resources\VolunteeringResource;
use App\Interfaces\Gateways\Api\User\FavoriteApiRepositoryInterface;
use App\Models\Event;
use App\Models\Favorite;
use App\Models\Feature;
use App\Models\GuideTrip;
use App\Models\Place;
use App\Models\Plan;
use App\Models\Trip;
use App\Models\User;
use App\Models\Volunteering;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use LevelUp\Experience\Models\Activity;

class EloquentFavoriteApiRepository implements FavoriteApiRepositoryInterface
{

    public function createFavorite($data)
    {
        $user=User::find($data['user_id']);
        $relationship = 'favorite' . ucfirst($data['type']).'s';
        if (!method_exists($user, $relationship)) {
            throw new \Exception(__("validation.api.relationship_not_exist", ['relationship' => $relationship]));
        }
        $user->{$relationship}()->attach($data['type_id']);
        $modelClass = 'App\Models\\' . ucfirst($data['type']);
        activityLog('favorite',$modelClass::find($data['type_id']) ,'the user add new favorite','create');
        $user->addPoints(10);
        $activity = Activity::find(1);
        $user->recordStreak($activity);
    }

    public function unfavored($data)
    {
        $user=User::find($data['user_id']);
        $relationship = 'favorite' . ucfirst($data['type']).'s';
        if (!method_exists($user, $relationship)) {
            throw new \Exception(__("validation.api.relationship_not_exist", ['relationship' => $relationship]));
        }
        $user->{$relationship}()->detach($data['type_id']);
        $modelClass = 'App\Models\\' . ucfirst($data['type']);
        activityLog('favorite',$modelClass::find($data['type_id']) ,'the user delete favorite','delete');
    }

    public function allUserFavorite()
    {
        $user = Auth::guard('api')->user();
        return new UserFavoriteResource($user);
    }

    public function favSearch($searchTerm)
    {
        $perPage = config('app.pagination_per_page');
        $user = Auth::guard('api')->user();

        $userLat = request()->lat ?? ($user && $user->latitude ? $user->latitude : null);
        $userLng = request()->lng ?? ($user && $user->longitude ? $user->longitude : null);

        $user = Auth::guard('api')->user();
        $userId = Auth::guard('api')->user()->id;
        // place search
        $places = Place::where('status', 1)
            ->whereHas('favoritedBy', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->selectRaw('places.*,
        (6371 * acos(
            cos(radians(?)) * cos(radians(places.latitude)) *
            cos(radians(places.longitude) - radians(?)) +
            sin(radians(?)) * sin(radians(places.latitude))
        )) AS distance', [$userLat, $userLng, $userLat])
            ->where(function ($query) use ($searchTerm) {
                $query->whereRaw('LOWER(JSON_UNQUOTE(JSON_EXTRACT(name, "$.en"))) LIKE ?', ['%' . strtolower($searchTerm) . '%'])
                    ->orWhereRaw('LOWER(JSON_UNQUOTE(JSON_EXTRACT(name, "$.ar"))) LIKE ?', ['%' . strtolower($searchTerm) . '%']);
            })
            ->orderBy('distance')
            ->paginate($perPage);

        $placesArray = $places->toArray();

        if ($userLat && $userLng) {
            $parameterNext = $placesArray['next_page_url'] ? $placesArray['next_page_url'] . '&lat=' . $userLat . "&lng=" . $userLng : $placesArray['next_page_url'];
            $parameterPrevious = $placesArray['prev_page_url'] ? $placesArray['prev_page_url'] . '&lat=' . $userLat . "&lng=" . $userLng : $placesArray['prev_page_url'];
        } else {
            $parameterNext = $placesArray['next_page_url'] ? $placesArray['next_page_url'] . '&lat=' . $userLat . "&lng=" . $userLng : null;
            $parameterPrevious = $placesArray['prev_page_url'] ? $placesArray['prev_page_url'] . '&lat=' . $userLat . "&lng=" . $userLng : null;
        }
        $paginationPlaces = [
            'next_page_url' => $parameterNext,
            'prev_page_url' => $parameterPrevious,
            'total'         => $placesArray['total'],
        ];
        //search of events
        $events = Event::whereHas('favoritedBy', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })
            ->where(function ($query) use ($searchTerm) {
                $query->whereRaw('LOWER(JSON_UNQUOTE(JSON_EXTRACT(name, "$.en"))) like ?', ['%' . strtolower($searchTerm) . '%'])
                    ->orWhereRaw('LOWER(JSON_UNQUOTE(JSON_EXTRACT(name, "$.ar"))) like ?', ['%' . strtolower($searchTerm) . '%']);
            })
            ->paginate($perPage);

        $eventsArray = $events->toArray();
        $paginationEvents = [
            'next_page_url' => $eventsArray['next_page_url'],
            'prev_page_url' => $eventsArray['prev_page_url'],
            'total'         => $eventsArray['total'],
        ];

        $volunteering = Volunteering::whereHas('favoritedBy', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })
            ->where(function ($query) use ($searchTerm) {
                $query->whereRaw('LOWER(JSON_UNQUOTE(JSON_EXTRACT(name, "$.en"))) like ?', ['%' . strtolower($searchTerm) . '%'])
                    ->orWhereRaw('LOWER(JSON_UNQUOTE(JSON_EXTRACT(name, "$.ar"))) like ?', ['%' . strtolower($searchTerm) . '%']);
            })
            ->paginate($perPage);

        $volunteeringArray = $volunteering->toArray();
        $paginationVolunteering = [
            'next_page_url' => $volunteeringArray['next_page_url'],
            'prev_page_url' => $volunteeringArray['prev_page_url'],
            'total'         => $volunteeringArray['total'],
        ];
//
//        //search of trips
        $trips = Trip::whereHas('favoritedBy', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })
            ->where(function ($query) use ($searchTerm) {
                $query->where('name', 'like', "%$searchTerm%");
            })
            ->whereHas('user', function ($query) {
                $query->where('status', '1');
            })
            ->paginate($perPage);

        $tripsArray = $trips->toArray();
        $paginationTrips = [
            'next_page_url' => $tripsArray['next_page_url'],
            'prev_page_url' => $tripsArray['prev_page_url'],
            'total'         => $tripsArray['total'],
        ];

        $guideTrips = GuideTrip::whereHas('favoritedBy', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })->where(function ($queryBuilder) use ($searchTerm) {
            $queryBuilder->whereRaw('LOWER(JSON_UNQUOTE(JSON_EXTRACT(name, "$.en"))) like ?', ['%' . strtolower($searchTerm) . '%'])
                ->orWhereRaw('LOWER(JSON_UNQUOTE(JSON_EXTRACT(name, "$.ar"))) like ?', ['%' . strtolower($searchTerm) . '%']);
        })
            ->whereHas('guide', function ($query) {
                $query->where('status', '1');
            })
            ->paginate($perPage);

        $guideTripsArray = $guideTrips->toArray();
        $paginationGuideTrips = [
            'next_page_url' => $guideTripsArray['next_page_url'],
            'prev_page_url' => $guideTripsArray['prev_page_url'],
            'total'         => $guideTripsArray['total'],
        ];

        $plans = Plan::whereHas('favoritedBy', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })
            ->where(function ($query) use ($searchTerm) {
                $query->whereRaw('LOWER(JSON_UNQUOTE(JSON_EXTRACT(name, "$.en"))) LIKE ?', ['%' . strtolower($searchTerm) . '%'])
                    ->orWhereRaw('LOWER(JSON_UNQUOTE(JSON_EXTRACT(name, "$.ar"))) LIKE ?', ['%' . strtolower($searchTerm) . '%']);
            })
            ->paginate($perPage);

        $plansArray = $plans->toArray();
        $paginationPlans = [
            'next_page_url' => $plansArray['next_page_url'],
            'prev_page_url' => $plansArray['prev_page_url'],
            'total'         => $plansArray['total'],
        ];
//
//        // Pass user coordinates to the PlaceResource collection
        return [

            'places' => [
                'data'       => PlaceResource::collection($places),
                'pagination' => $paginationPlaces,
            ],
            'events' => [
                'data'       => new ResourceCollection(EventResource::collection($events)),
                'pagination' => $paginationEvents,
            ],
            'guide_trips' => [
                'data'       => AllGuideTripResource::collection($guideTrips),
                'pagination' => $paginationGuideTrips,
            ],
            'volunteering' => [
                'data'       => new ResourceCollection(VolunteeringResource::collection($volunteering)),
                'pagination' => $paginationVolunteering,
            ],
            'trips' => [
                'data'       => TripResource::collection($trips),
                'pagination' => $paginationTrips,
            ],
            'plans' => [
                'data'       => PlanResource::collection($plans),
                'pagination' => $paginationPlans,
            ],
        ];


    }

    public function favSearchV2($searchTerm)
    {
        $user = Auth::guard('api')->user();
        // Filter favorite places
        $placeFav = $user->favoritePlaces
            ->filter(fn($place) =>
                $place->status == 1 && (stripos($place->name->ar, $searchTerm) !== false || stripos($place->name->en, $searchTerm) !== false))
            ->map(fn($place) => [
                'id'      => $place->id,
                'slug'    => $place->slug,
                'name'    => $place->name,
                'image'   => $place->getFirstMediaUrl('main_place', 'main_place_app'),
                'region'  => $place->region->name,
                'address' => $place->address,
            ]);

        // Filter favorite posts
        $postFav = $user->favoritePosts
            ->filter(fn($post) => $post->user->status == 1 &&
                stripos($post->content, $searchTerm) !== false)
            ->map(function ($post) {
                $gallery = $post->getMedia('post')->map(fn($image) => $image->original_url)->toArray();
                return [
                    'id' => $post->id,
                    'name' => $post->content,
                    'media' => $gallery,
                    'creator_id' => $post->user->id,
                    'creator_username' => $post->user->username,
                    'creator_slug' => $post->user->slug,
                    'visitable_type' => explode('\\Models\\', $post->visitable_type)[1] ?? null,
                    'visitable_id' => optional($post->visitable_type::find($post->visitable_id))->name,
                ];
            });

        // Filter other favorites
        $tripFav = $user->favoriteTrips
            ->filter(fn($trip) => $trip->user->status == 1 && stripos($trip->name, $searchTerm) !== false);

        $guideTripFav = $user->favoriteGuideTrips
            ->filter(fn($guideTrip) => $guideTrip->guide->status == 1 &&
                (stripos($guideTrip->name['ar'], $searchTerm) !== false ||
                    stripos($guideTrip->name['en'], $searchTerm) !== false));

        $planFav = $user->favoritePlans
            ->filter(fn($plan) => $plan->creator->status == 1 &&
                (stripos($plan->name['ar'], $searchTerm) !== false ||
                    stripos($plan->name['en'], $searchTerm) !== false));

        return [
            'places'       => $placeFav,
            'trip'         => TripResource::collection($tripFav),
            'event'        => EventResource::collection(
                $user->favoriteEvents->filter(fn($event) =>
                (stripos($event->name['ar'], $searchTerm) !== false ||
                    stripos($event->name['en'], $searchTerm) !== false))
            ),
            'volunteering' => VolunteeringResource::collection(
                $user->favoriteVolunteerings->filter(fn($vol) =>
                (stripos($vol->name['ar'], $searchTerm) !== false ||
                    stripos($vol->name['en'], $searchTerm) !== false))
            ),
            'plan'         => PlanResource::collection($planFav),
            'post'         => $postFav,
            'guide_trip'   => GuideFavoriteResource::collection($guideTripFav),
        ];
    }

}

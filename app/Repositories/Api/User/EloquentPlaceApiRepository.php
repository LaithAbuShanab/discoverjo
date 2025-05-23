<?php

namespace App\Repositories\Api\User;

use App\Http\Resources\AllGuideTripResource;
use App\Http\Resources\EventResource;
use App\Http\Resources\PlaceResource;
use App\Http\Resources\PlanResource;
use App\Http\Resources\SinglePlaceResource;
use App\Http\Resources\TripResource;
use App\Http\Resources\UserResource;
use App\Http\Resources\VolunteeringResource;
use App\Interfaces\Gateways\Api\User\PlaceApiRepositoryInterface;
use App\Models\Category;
use App\Models\Event;
use App\Models\Feature;
use App\Models\GuideTrip;
use App\Models\Place;
use App\Models\Plan;
use App\Models\Region;
use App\Models\Trip;
use App\Models\User;
use App\Models\Volunteering;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Facades\Auth;
use LevelUp\Experience\Models\Activity;


class EloquentPlaceApiRepository implements PlaceApiRepositoryInterface
{

    public function singlePlace($slug)
    {
        $place = Place::findBySlug($slug);
        activityLog('Place', $place, 'The user viewed place', 'view');
        return new SinglePlaceResource($place);
    }

    public function createVisitedPlace($slug)
    {
        $user = Auth::guard('api')->user();
        $place = Place::findBySlug($slug);
        $user->visitedPlace()->attach([$place->id]);
        activityLog('visited place', $place, 'The user create visited place', 'create');

        $user->addPoints(10);
        $activity = Activity::find(1);
        $user->recordStreak($activity);
    }

    public function deleteVisitedPlace($slug)
    {
        $user = Auth::guard('api')->user();
        $place = Place::findBySlug($slug);
        $user->visitedPlace()->detach($place->id);
        activityLog('visited place', $place, 'The user delete visited place', 'delete');

    }

    public function search($data)
    {
        $user = Auth::guard('api')->user();

        $userLat = isset($data['lat']) ? floatval($data['lat']) : ($user?->latitude !== null ? floatval($user->latitude) : null);
        $userLng = isset($data['lng']) ? floatval($data['lng']) : ($user?->longitude !== null ? floatval($user->longitude) : null);
        $query = $data['query'] ?? null;
        $perPage = config('app.pagination_per_page');

        if ($userLat !== null && $userLng !== null) {
            $placesQuery = Place::selectRaw(
                'places.*,
             (6371 * acos(
                cos(radians(?)) *
                cos(radians(places.latitude)) *
                cos(radians(places.longitude) - radians(?)) +
                sin(radians(?)) *
                sin(radians(places.latitude))
             )) AS distance',
                [$userLat, $userLng, $userLat]
            )
                ->where('status', 1)
                ->when($query, function ($q) use ($query) {
                    $q->where(function ($q2) use ($query) {
                        $q2->where('name_en', 'like', '%' . $query . '%')
                            ->orWhere('name_ar', 'like', '%' . $query . '%');
                    });
                })
                ->orderBy('distance');
        } else {
            $placesQuery = Place::select('places.*')
                ->selectRaw('NULL AS distance')
                ->where('status', 1)
                ->when($query, function ($q) use ($query) {
                    $q->where(function ($q2) use ($query) {
                        $q2->where('name_en', 'like', '%' . $query . '%')
                            ->orWhere('name_ar', 'like', '%' . $query . '%');
                    });
                });
        }

        $places = $placesQuery->paginate($perPage);
        $placesArray = $places->toArray();

        if ($userLat && $userLng) {
            $parameterNext = $placesArray['next_page_url'] ? $placesArray['next_page_url'] . '&lat=' . $userLat . "&lng=" . $userLng : $placesArray['next_page_url'];
            $parameterPrevious = $placesArray['prev_page_url'] ? $placesArray['prev_page_url'] . '&lat=' . $userLat . "&lng=" . $userLng : $placesArray['prev_page_url'];
        } else {
            $parameterNext = $placesArray['next_page_url'] ? $placesArray['next_page_url'] . '&lat=' . $userLat . "&lng=" . $userLng : null;
            $parameterPrevious = $placesArray['prev_page_url'] ? $placesArray['prev_page_url'] . '&lat=' . $userLat . "&lng=" . $userLng : null;
        }

        if ($query) {
            activityLog('Place', $places->first(), $query, 'search');
        }

        return [
            'places' => PlaceResource::collection($places),
            'pagination' => [
                'next_page_url' => $parameterNext,
                'prev_page_url' => $parameterPrevious,
                'total' => $placesArray['total'],
            ]
        ];
    }

    public function filter($data)
    {
        $user = Auth::guard('api')->user();

        $userLat = isset($data['lat']) ? floatval($data['lat']) : ($user?->latitude !== null ? floatval($user?->latitude) : null);
        $userLng = isset($data['lng']) ? floatval($data['lng']) : ($user?->longitude !== null ? floatval($user?->longitude) : null);
        $perPage =  config('app.pagination_per_page');

        // Decode inputs
        $categoriesSlugs = isset($data['categories']) ? explode(',', $data['categories']) : [];
        $subcategoriesSlugs = isset($data['subcategories']) ? explode(',', $data['subcategories']) : [];
        $featuresSlugs = isset($data['features']) ? explode(',', $data['features']) : [];
        $regionSlug = $data['region'] ?? null;
        $minCost = $data['min_cost'] ?? null;
        $maxCost = $data['max_cost'] ?? null;
        $minRating = $data['min_rate'] ?? null;
        $maxRating = $data['max_rate'] ?? null;

        // Retrieve IDs from database
        $categoriesIds = Category::whereIn('slug', $categoriesSlugs)->pluck('id');
        $subcategoriesIds = Category::whereIn('slug', $subcategoriesSlugs)->pluck('id');
        $featuresIds = Feature::whereIn('slug', $featuresSlugs)->pluck('id');
        $regionId = Region::where('slug', $regionSlug)->value('id');

        // Base query
        $query = Place::query()->where('status', 1);

        if ($categoriesIds->isNotEmpty() || $subcategoriesIds->isNotEmpty()) {
            $query->where(function ($subQuery) use ($categoriesIds, $subcategoriesIds) {
                if ($subcategoriesIds->isNotEmpty()) {
                    $subQuery->whereHas('categories', function ($subQuery) use ($subcategoriesIds) {
                        $subQuery->whereIn('place_categories.category_id', $subcategoriesIds);
                    });
                } else {
                    $subQuery->whereHas('categories', function ($subQuery) use ($categoriesIds) {
                        $subQuery->whereIn('place_categories.category_id', $categoriesIds)
                            ->orWhereIn('categories.parent_id', $categoriesIds);
                    });
                }
            });
        }

        // Apply filters
        $query->when($featuresIds->isNotEmpty(), function ($q) use ($featuresIds) {
            $q->whereHas('features', function ($subQuery) use ($featuresIds) {
                $subQuery->whereIn('features.id', $featuresIds);
            });
        });

        $query->when($regionId, function ($q) use ($regionId) {
            $q->where('region_id', $regionId);
        });

        $query->when($minCost, function ($q) use ($minCost) {
            $q->where('price_level', '>=', $minCost);
        });

        $query->when($maxCost, function ($q) use ($maxCost) {
            $q->where('price_level', '<=', $maxCost);
        });

        $query->when($minRating, function ($q) use ($minRating) {
            $q->where('rating', '>=', $minRating);
        });

        $query->when($maxRating, function ($q) use ($maxRating) {
            $q->where('rating', '<=', $maxRating);
        });

        // Apply distance sorting if coordinates are provided
        if ($userLat && $userLng) {
            $query->selectRaw(
                'places.*, ( 6371 * acos( cos( radians(?) ) * cos( radians( places.latitude ) ) * cos( radians( places.longitude ) - radians(?) ) + sin( radians(?) ) * sin( radians( places.latitude ) ) ) ) AS distance',
                [$userLat, $userLng, $userLat]
            )->orderBy('distance');
        }

        // Paginate results
        $places = $query->paginate($perPage);
        $placesArray = $places->toArray();
        $totalCount = (clone $query)->count();
        activityLog(
            'filter places',
            $query->first(),
            'The user filter the places',
            'filter',
            [
                'categories'     => $categoriesSlugs,
                'subcategories'  => $subcategoriesSlugs,
                'features'       => $featuresSlugs,
                'region'         => $regionSlug,
                'min_cost'       => $minCost,
                'max_cost'       => $maxCost,
                'min_rating'     => $minRating,
                'max_rating'     => $maxRating,
            ]
        );
        return [
            'count' => $totalCount,
            'places' => PlaceResource::collection($places),
            'pagination' => [
                'next_page_url' => $places->nextPageUrl(),
                'prev_page_url' => $places->previousPageUrl(),
                'total' => $placesArray['total'],
            ],
        ];
    }

    public function allSearch($query)
    {
        // Get user coordinates if provided
        $user = Auth::guard('api')->user();

        $userLat = request()->lat ?? ($user && $user->latitude ? $user->latitude : null);
        $userLng = request()->lng ?? ($user && $user->longitude ? $user->longitude : null);
        $perPage =  config('app.pagination_per_page');
        /**
         * SEARCH PLACES
         */
        $users = User::where('status', 1)->where(function ($queryBuilder) use ($query) {
            $queryBuilder->where('first_name', 'LIKE', "%{$query}%")
                ->orWhere('last_name', 'LIKE', "%{$query}%")
                ->orWhere('username', 'LIKE', "%{$query}%");
        })
            ->paginate($perPage);


        $usersArray = $users->toArray();
        $paginationUsers = [
            'next_page_url' => $usersArray['next_page_url'],
            'prev_page_url' => $usersArray['prev_page_url'],
            'total'         => $usersArray['total'],
        ];
        $places = Place::where('status', 1)->selectRaw(
            'places.*,
         (6371 * acos( cos( radians(?) ) * cos( radians(places.latitude) ) *
         cos( radians(places.longitude) - radians(?) ) + sin( radians(?) ) *
         sin( radians(places.latitude) ) )) AS distance',
            [$userLat, $userLng, $userLat]
        )
            ->where(function ($queryBuilder) use ($query) {
                // Only search within the "name" fields (both English and Arabic)
                $queryBuilder->whereRaw('LOWER(JSON_UNQUOTE(JSON_EXTRACT(name, "$.en"))) like ?', ['%' . strtolower($query) . '%'])
                    ->orWhereRaw('LOWER(JSON_UNQUOTE(JSON_EXTRACT(name, "$.ar"))) like ?', ['%' . strtolower($query) . '%']);
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

        /**
         * SEARCH EVENTS
         */
        $events = Event::where(function ($queryBuilder) use ($query) {
            $queryBuilder->whereRaw('LOWER(JSON_UNQUOTE(JSON_EXTRACT(name, "$.en"))) like ?', ['%' . strtolower($query) . '%'])
                ->orWhereRaw('LOWER(JSON_UNQUOTE(JSON_EXTRACT(name, "$.ar"))) like ?', ['%' . strtolower($query) . '%']);
        })
            ->paginate($perPage);

        $eventsArray = $events->toArray();
        $paginationEvents = [
            'next_page_url' => $eventsArray['next_page_url'],
            'prev_page_url' => $eventsArray['prev_page_url'],
            'total'         => $eventsArray['total'],
        ];

        /**
         * SEARCH GUIDE TRIPS
         */
        $guideTrips = GuideTrip::where(function ($queryBuilder) use ($query) {
            $queryBuilder->whereRaw('LOWER(JSON_UNQUOTE(JSON_EXTRACT(name, "$.en"))) like ?', ['%' . strtolower($query) . '%'])
                ->orWhereRaw('LOWER(JSON_UNQUOTE(JSON_EXTRACT(name, "$.ar"))) like ?', ['%' . strtolower($query) . '%']);
        })
            ->whereHas('guide', function ($query) {
                $query->where('status', '1'); // Ensures only active guides are included
            })
            ->paginate($perPage);


        $guideTripsArray = $guideTrips->toArray();
        $paginationGuideTrips = [
            'next_page_url' => $guideTripsArray['next_page_url'],
            'prev_page_url' => $guideTripsArray['prev_page_url'],
            'total'         => $guideTripsArray['total'],
        ];

        /**
         * SEARCH VOLUNTEERING
         */
        $volunteerings = Volunteering::where(function ($queryBuilder) use ($query) {
            $queryBuilder->whereRaw('LOWER(JSON_UNQUOTE(JSON_EXTRACT(name, "$.en"))) like ?', ['%' . strtolower($query) . '%'])
                ->orWhereRaw('LOWER(JSON_UNQUOTE(JSON_EXTRACT(name, "$.ar"))) like ?', ['%' . strtolower($query) . '%']);
        })
            ->paginate($perPage);

        $volunteeringsArray = $volunteerings->toArray();
        $paginationVolunteerings = [
            'next_page_url' => $volunteeringsArray['next_page_url'],
            'prev_page_url' => $volunteeringsArray['prev_page_url'],
            'total'         => $volunteeringsArray['total'],
        ];

        /**
         * SEARCH TRIPS (Non-guide trips)
         */
        $allTrips= null;
        if ($user) {
            $userId = $user->id;
            $userAge = Carbon::parse($user->birthday)->age;
            $userSex = $user->sex;

            // User's own trips matching search query
            $ownTrips = Trip::where('user_id', $userId)
                ->where(function ($q) use ($query) {
                    $q->where('name', 'like', "%$query%")
                        ->orWhere('description', 'like', "%$query%");
                })
                ->whereHas('user', fn($q) => $q->where('status', '1'));

            // Other users' trips matching search query
            $otherTrips = Trip::where('user_id', '!=', $userId)
                ->where(function ($q) use ($query) {
                    $q->where('name', 'like', "%$query%")
                        ->orWhere('description', 'like', "%$query%");
                })
                ->whereHas('user', fn($q) => $q->where('status', '1'))
                ->where(fn($q) => $this->applyTripTypeVisibility($q, $userId))
//                ->where(fn($q) => $this->applyCapacityCheck($q))
                ->where(fn($q) => $this->applySexAndAgeFilter($q, $userId, $userSex, $userAge));

            // Merge and paginate
            $allTrips = $ownTrips->union($otherTrips)
                ->orderBy('status', 'desc')
                ->orderBy('date_time', 'desc')
                ->paginate($perPage);
        } else {
            // Guest users see only public trips
            $allTrips = Trip::where('trip_type', 0)
                ->where(function ($q) use ($query) {
                    $q->where('name', 'like', "%$query%")
                        ->orWhere('description', 'like', "%$query%");
                })
                ->whereHas('user', fn($q) => $q->where('status', '1'))
                ->orderBy('status', 'desc')
                ->orderBy('date_time', 'desc')
                ->paginate($perPage);
        }

        $tripsArray = $allTrips->toArray();
        $paginationTrips = [
            'next_page_url' => $tripsArray['next_page_url'],
            'prev_page_url' => $tripsArray['prev_page_url'],
            'total' => $tripsArray['total'],
        ];

        /**
         * SEARCH PLANS
         */
        if (!Auth::guard('api')->user()) {
            $plans = Plan::where(function ($queryBuilder) use ($query) {
                $queryBuilder->whereRaw('LOWER(JSON_UNQUOTE(JSON_EXTRACT(name, "$.en"))) like ?', ['%' . strtolower($query) . '%'])
                    ->orWhereRaw('LOWER(JSON_UNQUOTE(JSON_EXTRACT(name, "$.ar"))) like ?', ['%' . strtolower($query) . '%']);
            })
                ->where('creator_type', 'App\Models\Admin')
                ->paginate($perPage);
        } else {
            $plans = Plan::where(function ($queryBuilder) use ($query) {
                $queryBuilder->whereRaw('LOWER(JSON_UNQUOTE(JSON_EXTRACT(name, "$.en"))) like ?', ['%' . strtolower($query) . '%'])
                    ->orWhereRaw('LOWER(JSON_UNQUOTE(JSON_EXTRACT(name, "$.ar"))) like ?', ['%' . strtolower($query) . '%']);
            })
                ->where(function ($queryBuilder) {
                    $queryBuilder->where('creator_type', 'App\Models\Admin')
                        ->orWhere(function ($queryBuilder) {
                            $queryBuilder->where('creator_type', 'App\Models\User')
                                ->where('creator_id', Auth::guard('api')->user()->id);
                        });
                })
                ->paginate($perPage);
        }
        $plansArray = $plans->toArray();
        $paginationPlans = [
            'next_page_url' => $plansArray['next_page_url'],
            'prev_page_url' => $plansArray['prev_page_url'],
            'total'         => $plansArray['total'],
        ];

        if($query) {
            activityLog('all search for places', $places->first(), $query, 'search');
        }

        /**
         * Combine all results in one JSON response.
         */
        return [
            'users' => [
                'data'       => UserResource::collection($users),
                'pagination' => $paginationUsers,
            ],
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
                'data'       => new ResourceCollection(VolunteeringResource::collection($volunteerings)),
                'pagination' => $paginationVolunteerings,
            ],
            'trips' => [
                'data'       => TripResource::collection($allTrips),
                'pagination' => $paginationTrips,
            ],
            'plans' => [
                'data'       => PlanResource::collection($plans),
                'pagination' => $paginationPlans,
            ],
        ];
    }

    private function applyTripTypeVisibility($query, $userId)
    {
        $query->where('trip_type', '0') // Public
        ->orWhere(function ($q) use ($userId) {
            $q->where('trip_type', '1') // Followers
            ->where(function ($q) use ($userId) {
                $q->whereHas('user.followers', fn($q) => $q->where('follower_id', $userId))
                    ->orWhere('user_id', $userId);
            });
        })
            ->orWhere(function ($q) use ($userId) {
                $q->where('trip_type', '2') // Specific
                ->whereHas('usersTrip', fn($q) => $q->where('user_id', $userId)->where('status', '1'))
                    ->orWhere('user_id', $userId);
            });

        return $query;
    }

    private function applySexAndAgeFilter($query, $userId, $userSex, $userAge)
    {
        $query->where(function ($q) use ($userId, $userSex, $userAge) {
            $q->where('user_id', $userId) // صاحب الرحلة
            ->orWhere(function ($q) use ($userSex, $userAge) {
                $q->where('trip_type', '2') // المخصصة
                ->orWhere(function ($q) use ($userSex, $userAge) {
                    $q->whereIn('sex', [$userSex, 0])
                        ->where(function ($q) use ($userAge) {
                            $q->whereNull('age_range')
                                ->orWhere(function ($q) use ($userAge) {
                                    $q->whereRaw('CAST(JSON_UNQUOTE(JSON_EXTRACT(age_range, "$.min")) AS UNSIGNED) <= ?', [$userAge])
                                        ->whereRaw('CAST(JSON_UNQUOTE(JSON_EXTRACT(age_range, "$.max")) AS UNSIGNED) >= ?', [$userAge]);
                                });
                        });
                });
            });
        });

        return $query;
    }

}

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
use App\Models\GuideTrip;
use App\Models\Place;
use App\Models\Plan;
use App\Models\Reviewable;
use App\Models\Trip;
use App\Models\User;
use App\Models\Volunteering;
use App\Notifications\Users\review\NewReviewDisLikeNotification;
use App\Notifications\Users\review\NewReviewLikeNotification;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Notification;


class EloquentPlaceApiRepository implements PlaceApiRepositoryInterface
{

    public function singlePlace($slug)
    {
        $place = Place::findBySlug($slug);
        return new SinglePlaceResource($place);
    }

    public function createFavoritePlace($data)
    {
        $user = User::find($data['user_id']);
        $user->favoritePlaces()->attach([$data['place_id']]);
    }

    public function deleteFavoritePlace($id)
    {
        $user = Auth::guard('api')->user();
        $user->favoritePlaces()->detach($id);
    }

    public function createVisitedPlace($data)
    {
        $user = User::find($data['user_id']);
        $user->visitedPlace()->attach([$data['place_id']]);
    }

    public function deleteVisitedPlace($id)
    {
        $user = Auth::guard('api')->user();
        $user->visitedPlace()->detach($id);
    }

    public function addReview($data)
    {
        $user = Auth::guard('api')->user();
        $user->reviewPlace()->attach($data['place_id'], [
            'rating' => $data['rating'],
            'comment' => $data['comment']
        ]);
    }

    public function updateReview($data)
    {
        $user = Auth::guard('api')->user();
        $user->reviewPlace()->sync([$data['place_id'] => [
            'rating' => $data['rating'],
            'comment' => $data['comment']
        ]]);
    }

    public function deleteReview($id)
    {
        $user = Auth::guard('api')->user();
        $user->reviewPlace()->detach($id);
    }

    public function reviewsLike($request)
    {
        $review = Reviewable::find($request->review_id);
        $status = $request->status == "like" ? '1' : '0';
        $userReview = $review->user;
        $receiverLanguage = $userReview->lang;
        $ownerToken = $userReview->DeviceToken->token;
        $notificationData = [];

        $existingLike = $review->like()->where('user_id', Auth::guard('api')->user()->id)->first();

        if ($existingLike) {
            if ($existingLike->pivot->status != $status) {
                $review->like()->updateExistingPivot(Auth::guard('api')->user()->id, ['status' => $status]);
                if ($request->status == "like") {
                    $notificationData = [
                        'title' => Lang::get('app.notifications.new-review-like', [], $receiverLanguage),
                        'body' => Lang::get('app.notifications.new-user-like-in-review', ['username' => Auth::guard('api')->user()->username], $receiverLanguage),
                        'sound' => 'default',
                    ];
                    Notification::send($userReview, new NewReviewLikeNotification(Auth::guard('api')->user()));
                } else {
                    $notificationData = [
                        'title' => Lang::get('app.notifications.new-review-dislike', [], $receiverLanguage),
                        'body' => Lang::get('app.notifications.new-user-dislike-in-review', ['username' => Auth::guard('api')->user()->username], $receiverLanguage),
                        'sound' => 'default',
                    ];

                    Notification::send($userReview, new NewReviewDisLikeNotification(Auth::guard('api')->user()));
                }
            } else {
                $review->like()->detach(Auth::guard('api')->user()->id);
            }
        } else {
            $review->like()->attach(Auth::guard('api')->user()->id, ['status' => $status]);
            if ($request->status == "like") {
                $notificationData = [
                    'title' => Lang::get('app.notifications.new-review-like', [], $receiverLanguage),
                    'body' => Lang::get('app.notifications.new-user-like-in-review', ['username' => Auth::guard('api')->user()->username], $receiverLanguage),
                    'sound' => 'default',
                ];
                Notification::send($userReview, new NewReviewLikeNotification(Auth::guard('api')->user()));
            } else {
                $notificationData = [
                    'title' => Lang::get('app.notifications.new-review-dislike', [], $receiverLanguage),
                    'body' => Lang::get('app.notifications.new-user-dislike-in-review', ['username' => Auth::guard('api')->user()->username], $receiverLanguage),
                    'sound' => 'default',
                ];

                Notification::send($userReview, new NewReviewDisLikeNotification(Auth::guard('api')->user()));
            }
        }
        sendNotification($ownerToken, $notificationData);
    }

    public function search($query)
    {
        $userLat = request()->lat ? request()->lat : null;
        $userLng = request()->lng ? request()->lng : null;
        $perPage = 20;
        $places = Place::selectRaw(
            'places.*,
         ( 6371 * acos( cos( radians(?) ) * cos( radians( places.latitude ) ) * cos( radians( places.longitude ) - radians(?) ) + sin( radians(?) ) * sin( radians( places.latitude ) ) ) ) AS distance',
            [$userLat, $userLng, $userLat]
        )
            ->where(function ($queryBuilder) use ($query) {
                $queryBuilder->whereRaw('LOWER(JSON_UNQUOTE(JSON_EXTRACT(name, "$.en"))) like ?', ['%' . strtolower($query) . '%'])
                    ->orWhereRaw('LOWER(JSON_UNQUOTE(JSON_EXTRACT(name, "$.ar"))) like ?', ['%' . strtolower($query) . '%'])
                    ->orWhereRaw('LOWER(JSON_UNQUOTE(JSON_EXTRACT(description, "$.en"))) like ?', ['%' . strtolower($query) . '%'])
                    ->orWhereRaw('LOWER(JSON_UNQUOTE(JSON_EXTRACT(description, "$.ar"))) like ?', ['%' . strtolower($query) . '%']);
            }) ->orderBy('distance') // Sort by distance
            ->paginate($perPage);

        $placesArray = $places->toArray();

        if($userLat &&$userLng ){
            $parameterNext = $placesArray['next_page_url']?$placesArray['next_page_url'].'&lat='.$userLat."&lng=".$userLng:$placesArray['next_page_url'];
            $parameterPrevious = $placesArray['prev_page_url']?$placesArray['prev_page_url'].'&lat='.$userLat."&lng=".$userLng:$placesArray['prev_page_url'];
        }else{
            $parameterNext = $placesArray['next_page_url']?$placesArray['next_page_url'].'&lat='.$userLat."&lng=".$userLng:null;
            $parameterPrevious = $placesArray['prev_page_url']?$placesArray['prev_page_url'].'&lat='.$userLat."&lng=".$userLng:null;
        }

        // Convert pagination result to array and include pagination metadata

        $pagination = [
            'next_page_url'=>$parameterNext,
            'prev_page_url'=>$parameterPrevious,
            'total' => $placesArray['total'],
        ];

        // Pass user coordinates to the PlaceResource collection
        return [
            'places' => PlaceResource::collection($places),
            'pagination' => $pagination
        ];

    }

    public function filter($data)
    {
        $userLat = request()->lat ?? null;
        $userLng = request()->lng ?? null;
        $perPage = 20;

        // Decode JSON inputs
        $subcategoriesIds = isset($data['subcategories_id'])?json_decode($data['subcategories_id']) ?? []:[];
        $featuresIds = isset($data['features_id'])?json_decode($data['features_id']) ?? []:[];
        $categoriesIds =  isset($data['categories_id'])?json_decode($data['categories_id']) ?? []:[];
        $regionId = isset($data['region_id'])?$data['region_id'] : null;
        $minCost =isset($data['min_cost'])? $data['min_cost'] : null;
        $maxCost = isset($data['max_cost'])?$data['max_cost'] : null;
        $minRating = isset($data['min_rate'])? $data['min_rate'] : null;
        $maxRating =isset($data['max_rate'])? $data['max_rate'] : null;

        // Base query for filtering places
        $query = Place::query();


        if($subcategoriesIds || $categoriesIds){
            $query->where(function ($subQuery) use ($subcategoriesIds, $categoriesIds) {
                if (!empty($subcategoriesIds)) {
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
        if($featuresIds) {
            $query->whereHas('features', function ($subQuery) use ($featuresIds) {
                $subQuery->whereIn('features.id', $featuresIds);
            });
        }

        if($regionId) {
            $query->when($regionId, function ($subQuery) use ($regionId) {
                $subQuery->where('region_id', $regionId);
            });
        }

        if($minCost){
            $query->when($minCost !== null, function ($subQuery) use ($minCost) {
                $subQuery->where('price_level', '>=', $minCost);
            });
        }

        if($maxCost){
            $query->when($maxCost !== null, function ($subQuery) use ($maxCost) {
                $subQuery->where('price_level', '<=', $maxCost);
            });
        }

        if($minRating){
            $query->when($minRating !== null, function ($subQuery) use ($minRating) {
                $subQuery->where('rating', '>=', $minRating);
            });
        }

        if($maxRating){
            $query->when($maxRating !== null, function ($subQuery) use ($maxRating) {
                $subQuery->where('rating', '<=', $maxRating);
            });
        }


        // Apply distance calculation if user coordinates are provided
        if ($userLat && $userLng) {
            $query->selectRaw(
                'places.*, ( 6371 * acos( cos( radians(?) ) * cos( radians( places.latitude ) ) * cos( radians( places.longitude ) - radians(?) ) + sin( radians(?) ) * sin( radians( places.latitude ) ) ) ) AS distance',
                [$userLat, $userLng, $userLat]
            )
                ->orderBy('distance'); // Sort by distance
        }

        // Paginate the results
        $places = $query->paginate($perPage);

        // Prepare pagination URLs
        $placesArray = $places->toArray();
        if($userLat &&$userLng ){
            $parameterNext = $placesArray['next_page_url']?$placesArray['next_page_url'].'&lat='.$userLat."&lng=".$userLng:$placesArray['next_page_url'];
            $parameterPrevious = $placesArray['prev_page_url']?$placesArray['prev_page_url'].'&lat='.$userLat."&lng=".$userLng:$placesArray['prev_page_url'];
        }else{
            $parameterNext = $placesArray['next_page_url']?$placesArray['next_page_url'].'&lat='.$userLat."&lng=".$userLng:null;
            $parameterPrevious = $placesArray['prev_page_url']?$placesArray['prev_page_url'].'&lat='.$userLat."&lng=".$userLng:null;
        }


        // Convert pagination result to array and include pagination metadata
        $pagination = [
            'next_page_url'=>$parameterNext,
            'prev_page_url'=>$parameterPrevious,
            'total' => $placesArray['total'],
        ];

        // Pass user coordinates to the PlaceResource collection
        return [
            'places' => PlaceResource::collection($places),
            'pagination' => $pagination
        ];
    }

    public function allSearch($query)
    {
        // Get user coordinates if provided
        $userLat = request()->lat ? request()->lat : null;
        $userLng = request()->lng ? request()->lng : null;

        /**
         * SEARCH PLACES
         */
        $perPageUsers=20;
        $users = User::whereRaw("MATCH (first_name, last_name, username) AGAINST (? IN NATURAL LANGUAGE MODE)", [$query])
            ->paginate($perPageUsers);

        $usersArray = $users->toArray();
        $paginationUsers = [
            'next_page_url' => $usersArray['next_page_url'],
            'prev_page_url' => $usersArray['prev_page_url'],
            'total'         => $usersArray['total'],
        ];
        $perPagePlaces = 20;
        $places = Place::selectRaw(
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
            ->paginate($perPagePlaces);

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
        $perPageEvents = 15;
        $events = Event::where(function ($queryBuilder) use ($query) {
            $queryBuilder->whereRaw('LOWER(JSON_UNQUOTE(JSON_EXTRACT(name, "$.en"))) like ?', ['%' . strtolower($query) . '%'])
                ->orWhereRaw('LOWER(JSON_UNQUOTE(JSON_EXTRACT(name, "$.ar"))) like ?', ['%' . strtolower($query) . '%']);
        })
            ->paginate($perPageEvents);

        $eventsArray = $events->toArray();
        $paginationEvents = [
            'next_page_url' => $eventsArray['next_page_url'],
            'prev_page_url' => $eventsArray['prev_page_url'],
            'total'         => $eventsArray['total'],
        ];

        /**
         * SEARCH GUIDE TRIPS
         */
        $perPageGuideTrips = 15;
        $guideTrips = GuideTrip::where(function ($queryBuilder) use ($query) {
            $queryBuilder->whereRaw('LOWER(JSON_UNQUOTE(JSON_EXTRACT(name, "$.en"))) like ?', ['%' . strtolower($query) . '%'])
                ->orWhereRaw('LOWER(JSON_UNQUOTE(JSON_EXTRACT(name, "$.ar"))) like ?', ['%' . strtolower($query) . '%']);
        })
            ->paginate($perPageGuideTrips);

        $guideTripsArray = $guideTrips->toArray();
        $paginationGuideTrips = [
            'next_page_url' => $guideTripsArray['next_page_url'],
            'prev_page_url' => $guideTripsArray['prev_page_url'],
            'total'         => $guideTripsArray['total'],
        ];

        /**
         * SEARCH VOLUNTEERING
         */
        $perPageVolunteerings = 15;
        $volunteerings = Volunteering::where(function ($queryBuilder) use ($query) {
            $queryBuilder->whereRaw('LOWER(JSON_UNQUOTE(JSON_EXTRACT(name, "$.en"))) like ?', ['%' . strtolower($query) . '%'])
                ->orWhereRaw('LOWER(JSON_UNQUOTE(JSON_EXTRACT(name, "$.ar"))) like ?', ['%' . strtolower($query) . '%']);
        })
            ->paginate($perPageVolunteerings);

        $volunteeringsArray = $volunteerings->toArray();
        $paginationVolunteerings = [
            'next_page_url' => $volunteeringsArray['next_page_url'],
            'prev_page_url' => $volunteeringsArray['prev_page_url'],
            'total'         => $volunteeringsArray['total'],
        ];

        /**
         * SEARCH TRIPS (Non-guide trips)
         */
        $perPageTrips = 15;
        $trips = Trip::where('name', 'like', "%$query%")
            // Removed the description condition as requested.
            ->paginate($perPageTrips);

        $tripsArray = $trips->toArray();
        $paginationTrips = [
            'next_page_url' => $tripsArray['next_page_url'],
            'prev_page_url' => $tripsArray['prev_page_url'],
            'total'         => $tripsArray['total'],
        ];

        /**
         * SEARCH PLANS
         */
        $perPagePlans = 15;
        if (!Auth::guard('api')->user()) {
            $plans = Plan::where(function ($queryBuilder) use ($query) {
                $queryBuilder->whereRaw('LOWER(JSON_UNQUOTE(JSON_EXTRACT(name, "$.en"))) like ?', ['%' . strtolower($query) . '%'])
                    ->orWhereRaw('LOWER(JSON_UNQUOTE(JSON_EXTRACT(name, "$.ar"))) like ?', ['%' . strtolower($query) . '%']);
            })
                ->where('creator_type', 'App\Models\Admin')
                ->paginate($perPagePlans);
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
                ->paginate($perPagePlans);
        }
        $plansArray = $plans->toArray();
        $paginationPlans = [
            'next_page_url' => $plansArray['next_page_url'],
            'prev_page_url' => $plansArray['prev_page_url'],
            'total'         => $plansArray['total'],
        ];

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
                'data'       => TripResource::collection($trips),
                'pagination' => $paginationTrips,
            ],
            'plans' => [
                'data'       => PlanResource::collection($plans),
                'pagination' => $paginationPlans,
            ],
        ];
    }


}

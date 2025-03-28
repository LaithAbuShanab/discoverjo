<?php

namespace App\Repositories\Api\User;

use App\Http\Resources\AllCategoriesResource;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\CurrentLocationPlacesResource;
use App\Http\Resources\OtherUserProfileResource;
use App\Http\Resources\PlaceResource;
use App\Http\Resources\TagResource;
use App\Http\Resources\UserFavoriteResource;
use App\Http\Resources\UserFavoriteSearchResource;
use App\Http\Resources\UserNotificationResource;
use App\Http\Resources\UserProfileResource;
use App\Http\Resources\UserResource;
use App\Interfaces\Gateways\Api\User\CategoryApiRepositoryInterface;
use App\Interfaces\Gateways\Api\User\UserProfileApiRepositoryInterface;
use App\Models\Category;
use App\Models\Notification;
use App\Models\Place;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Notifications\DatabaseNotification;



class EloquentUserProfileApiRepository implements UserProfileApiRepositoryInterface
{
    public function getUserDetails()
    {
        $userId = Auth::guard('api')->user()->id;
        $eloquentUser = User::find($userId);
        return new UserProfileResource($eloquentUser);
    }

    public function updateProfile($request, $tags, $userImage)
    {
        $userId = Auth::guard('api')->user()->id;
        $eloquentUser = User::find($userId);
        $eloquentUser->update($request);
        // ======================= Tags =======================
        $tagIds = Tag::whereIn('slug', $tags)->pluck('id');
        $eloquentUser->tags()->sync($tagIds);

        if ($userImage !== null) {
            $extension = pathinfo($userImage->getClientOriginalName(), PATHINFO_EXTENSION);
            $filename = Str::random(10) . '_' . time() . '.' . $extension;
            $eloquentUser->addMediaFromRequest('image')->usingFileName($filename)->toMediaCollection('avatar');
        }
        return new UserProfileResource($eloquentUser);
    }

    public function setLocation($request)
    {
        $userId = Auth::guard('api')->user()->id;
        $eloquentUser = User::find($userId);
        $eloquentUser->update([
            'latitude'=>$request['latitude'],
            'longitude'=>$request['longitude'],

        ]);
        $translator = ['en' => getAddressFromCoordinates($request['latitude'],$request['longitude'],"en"), 'ar' => getAddressFromCoordinates($request['latitude'],$request['longitude'],"ar")];
        $eloquentUser->setTranslations('address', $translator);
        $eloquentUser->save();

    }

    public function allFavorite()
    {
        $user = Auth::guard('api')->user();
        return new UserFavoriteResource($user);
    }

    public function search($query)
    {
        $perPage = config('app.pagination_per_page');

        $users = User::where('status',1)->where(function ($queryBuilder) use ($query) {
            $queryBuilder->where('first_name', 'LIKE', "%{$query}%")
                ->orWhere('last_name', 'LIKE', "%{$query}%")
                ->orWhere('username', 'LIKE', "%{$query}%");
        })
            ->paginate($perPage);


        $usersArray = $users->toArray();

        $pagination = [
            'next_page_url'=>$usersArray['next_page_url'],
            'prev_page_url'=>$usersArray['next_page_url'],
            'total' => $usersArray['total'],
        ];
        if($query) {
            activityLog('user', $users->first(), $query, 'search');
        }
        // Pass user coordinates to the PlaceResource collection
        return [
            'users' => UserResource::collection($users),
            'pagination' => $pagination
        ];
    }

    public function favSearch($searchTerm)
    {
        $perPage = config('app.pagination_per_page');

        $userId = Auth::guard('api')->user()->id;
        // Retrieve all possibilities that could be in user's favorites and match the search term
        $favorites = DB::table('favorables')
            ->where('favorables.user_id', $userId) // Qualify user_id with table name
            ->leftJoin('plans', function ($join) {
                $join->on('favorables.favorable_id', '=', 'plans.id')
                    ->where('favorables.favorable_type', '=', 'App\Models\Plan');
            })
            ->leftJoin('trips', function ($join) {
                $join->on('favorables.favorable_id', '=', 'trips.id')
                    ->where('favorables.favorable_type', '=', 'App\Models\Trip');
            })
            ->leftJoin('places', function ($join) {
                $join->on('favorables.favorable_id', '=', 'places.id')
                    ->where('favorables.favorable_type', '=', 'App\Models\Place');
            })
            ->leftJoin('events', function ($join) {
                $join->on('favorables.favorable_id', '=', 'events.id')
                    ->where('favorables.favorable_type', '=', 'App\Models\Event');
            })
            ->leftJoin('volunteerings', function ($join) {
                $join->on('favorables.favorable_id', '=', 'volunteerings.id')
                    ->where('favorables.favorable_type', '=', 'App\Models\Volunteering');
            })
            ->leftJoin('posts', function ($join) {
                $join->on('favorables.favorable_id', '=', 'posts.id')
                    ->where('favorables.favorable_type', '=', 'App\Models\Post');
            })
            ->where(function ($query) use ($searchTerm) {
                $query->WhereRaw('LOWER(JSON_UNQUOTE(JSON_EXTRACT(plans.name, "$.ar"))) like ?', ['%' . strtolower($searchTerm) . '%'])
                    ->orWhereRaw('LOWER(JSON_UNQUOTE(JSON_EXTRACT(plans.name, "$.en"))) like ?', ['%' . strtolower($searchTerm) . '%'])
                    ->orWhere('trips.name', 'like', '%' . $searchTerm . '%')
                    ->orWhereRaw('LOWER(JSON_UNQUOTE(JSON_EXTRACT(places.name, "$.en"))) like ?', ['%' . strtolower($searchTerm) . '%'])
                    ->orWhereRaw('LOWER(JSON_UNQUOTE(JSON_EXTRACT(places.name, "$.ar"))) like ?', ['%' . strtolower($searchTerm) . '%'])
                    ->orWhereRaw('LOWER(JSON_UNQUOTE(JSON_EXTRACT(events.name, "$.en"))) like ?', ['%' . strtolower($searchTerm) . '%'])
                    ->orWhereRaw('LOWER(JSON_UNQUOTE(JSON_EXTRACT(events.name, "$.ar"))) like ?', ['%' . strtolower($searchTerm) . '%'])
                    ->orWhereRaw('LOWER(JSON_UNQUOTE(JSON_EXTRACT(volunteerings.name, "$.en"))) like ?', ['%' . strtolower($searchTerm) . '%'])
                    ->orWhereRaw('LOWER(JSON_UNQUOTE(JSON_EXTRACT(volunteerings.name, "$.ar"))) like ?', ['%' . strtolower($searchTerm) . '%'])
                    ->orWhere('posts.content', 'like', '%' . $searchTerm . '%');
            })
            ->select('favorables.*')
            ->paginate($perPage);

        $favoritesArray = $favorites->toArray();

        $pagination = [
            'next_page_url'=>$favoritesArray['next_page_url'],
            'prev_page_url'=>$favoritesArray['next_page_url'],
            'total' => $favoritesArray['total'],
        ];

        // Pass user coordinates to the PlaceResource collection
        return [
            'favorites' => UserFavoriteSearchResource::collection($favorites),
            'pagination' => $pagination
        ];


    }

    public function allTags()
    {
        $tags = Tag::all();

        return TagResource::collection($tags);
    }

    public function otherUserDetails($slug)
    {
        $eloquentUser = User::findBySlug($slug);
        $userSlug = Auth::guard('api')->user()->slug;
        if($userSlug == $slug){
            return new UserProfileResource($eloquentUser);
        }else{
            activityLog('user',$eloquentUser,'the current user view this user','view');
            return new OtherUserProfileResource($eloquentUser);
        }
    }

    public function PlacesCurrentLocation($request)
    {
        $userLat = $request['lat'];
        $userLng = $request['lng'];
        $distanceKm = $request['area'] ?? 15;

        $categoriesSlugs = isset($request['categories']) ? explode(',', $request['categories']) : [];
        $subcategoriesSlugs = isset($request['subcategories']) ? explode(',', $request['subcategories']) : [];

        $categoriesIds = Category::whereIn('slug', $categoriesSlugs)->pluck('id')->toArray();
        $subcategoriesIds = Category::whereIn('slug', $subcategoriesSlugs)->pluck('id')->toArray();


        $query = Place::where('status',1)->selectRaw(
            'places.*, ( 6371 * acos( cos( radians(?) ) * cos( radians( places.latitude ) ) * cos( radians( places.longitude ) - radians(?) ) + sin( radians(?) ) * sin( radians( places.latitude ) ) ) ) AS distance',
            [$userLat, $userLng, $userLat]
        )->having('distance', '<=', $distanceKm)->orderBy('distance', 'asc');

        // Apply category and subcategory filters
        if (!empty($subcategoriesIds) || !empty($categoriesIds)) {
            $query->where(function ($subQuery) use ($subcategoriesIds, $categoriesIds) {
                if (!empty($subcategoriesIds)) {
                    $subQuery->whereHas('categories', function ($subQuery) use ($subcategoriesIds) {
                        $subQuery->whereIn('place_categories.category_id', $subcategoriesIds);
                    });
                }
                else {
                    $subQuery->whereHas('categories', function ($subQuery) use ($categoriesIds) {
                        $subQuery->whereIn('place_categories.category_id', $categoriesIds)
                            ->orWhereIn('categories.parent_id', $categoriesIds);
                    });
                }
            });
        }


        // Execute the query and paginate
        // Paginate the results
//        $places = $query->limit(50)->get();
        $places = $query->get();
        activityLog(
            'places around',
            $places->first(),
            'The user viewed places around.',
            'find',
            [
                'categories' => $request['categories'] ?? null,
                'subcategories' => $request['subcategories'] ?? null,
                'area' => $request['area'] ?? null,
            ]
        );

       return CurrentLocationPlacesResource::collection($places);

    }

    public function allNotifications()
    {
        $user= Auth::guard('api')->user();
        $notifications = DatabaseNotification::where('notifiable_type','App\Models\User')->where('notifiable_id',$user->id)->orderBy('created_at', 'desc')->get();
        return  UserNotificationResource::collection($notifications);
    }

    public function readNotification($id)
    {
        $user= Auth::guard('api')->user();
        $notification = $user->notifications()->where('id', $id)->first();
        $notification->markAsRead();
        return $notification;
    }
}

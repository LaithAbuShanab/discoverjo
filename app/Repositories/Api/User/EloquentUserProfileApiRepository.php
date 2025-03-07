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
use App\Http\Resources\UserProfileResource;
use App\Http\Resources\UserResource;
use App\Interfaces\Gateways\Api\User\CategoryApiRepositoryInterface;
use App\Interfaces\Gateways\Api\User\UserProfileApiRepositoryInterface;
use App\Models\Category;
use App\Models\Place;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;


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
        $eloquentUser->tags()->sync(array_values($tags));

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
        $translator = ['en' => $request['address_en'], 'ar' => $request['address_ar']];
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
        $perPage =15;

        $users = User::whereRaw("MATCH (first_name, last_name, username) AGAINST (? IN NATURAL LANGUAGE MODE)", [$query])
            ->paginate($perPage);

        $usersArray = $users->toArray();

        $pagination = [
            'next_page_url'=>$usersArray['next_page_url'],
            'prev_page_url'=>$usersArray['next_page_url'],
            'total' => $usersArray['total'],
        ];

        // Pass user coordinates to the PlaceResource collection
        return [
            'users' => UserResource::collection($users),
            'pagination' => $pagination
        ];
    }

    public function favSearch($searchTerm)
    {
        $perPage =15;

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

    public function otherUserDetails($id)
    {
        $eloquentUser = User::find($id);
        $userId = Auth::guard('api')->user()->id;
        if($userId == $id){
            return new UserProfileResource($eloquentUser);
        }else{
            if($eloquentUser->status != '1'){
                throw new \Exception('this user inactive');
            }
            return new OtherUserProfileResource($eloquentUser);
        }


    }

    public function PlacesCurrentLocation($request)
    {
        $userLat = $request['lat'];
        $userLng = $request['lng'];
        $distanceKm = $request['area'] ?? 15;

        // Decode JSON inputs
        $subcategoriesIds = isset($request['subcategories_id'])? json_decode($request['subcategories_id']) ?? [] :null;
        $categoriesIds = isset($request['categories_id'])?json_decode($request['categories_id']) ?? []:null;

        $query = Place::selectRaw(
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
                } else {
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

       return CurrentLocationPlacesResource::collection($places);

    }
}

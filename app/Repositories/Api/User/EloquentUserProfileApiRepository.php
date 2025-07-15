<?php

namespace App\Repositories\Api\User;

use App\Http\Resources\CurrentLocationPlacesResource;
use App\Http\Resources\OtherUserProfileResource;
use App\Http\Resources\TagResource;
use App\Http\Resources\UserFavoriteResource;
use App\Http\Resources\UserFavoriteSearchResource;
use App\Http\Resources\UserNotificationResource;
use App\Http\Resources\UserProfileResource;
use App\Http\Resources\UserResource;
use App\Interfaces\Gateways\Api\User\UserProfileApiRepositoryInterface;
use App\Models\Category;
use App\Models\Place;
use App\Models\Tag;
use App\Models\User;
use App\Models\Warning;
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
        DB::beginTransaction();

        try {
            $userId = Auth::guard('api')->user()->id;
            $eloquentUser = User::find($userId);

            $eloquentUser->update([
                'latitude' => $request['latitude'],
                'longitude' => $request['longitude'],
            ]);

            $translator = [
                'en' => getAddressFromCoordinates($request['latitude'], $request['longitude'], "en"),
                'ar' => getAddressFromCoordinates($request['latitude'], $request['longitude'], "ar"),
            ];

            $eloquentUser->setTranslations('address', $translator);

            $eloquentUser->save();

            DB::commit();

            return response()->json(['message' => 'Location updated successfully', 'user' => $eloquentUser]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to update location', 'message' => $e->getMessage()], 500);
        }
    }

    public function allFavorite()
    {
        $user = Auth::guard('api')->user();
        return new UserFavoriteResource($user);
    }

    public function search($query)
    {
        $perPage = config('app.pagination_per_page');
        //        $quotedQuery= DB::getPdo()->quote($query);
        $users = User::where('status', 1)
            ->where(function ($q) use ($query) {
                $q->where('first_name', 'like', "%{$query}%")
                    ->orWhere('last_name', 'like', "%{$query}%")
                    ->orWhere('username', 'like', "%{$query}%");
            })
            ->paginate($perPage);

        $usersArray = $users->toArray();

        $pagination = [
            'next_page_url' => $usersArray['next_page_url'],
            'prev_page_url' => $usersArray['next_page_url'],
            'total' => $usersArray['total'],
        ];
        if ($query) {
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
            'next_page_url' => $favoritesArray['next_page_url'],
            'prev_page_url' => $favoritesArray['next_page_url'],
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
        if ($userSlug == $slug) {
            return new UserProfileResource($eloquentUser);
        } else {
            activityLog('user', $eloquentUser, 'the current user view this user', 'view');
            return new OtherUserProfileResource($eloquentUser);
        }
    }

    public function PlacesCurrentLocation($request)
    {
        $user = Auth::guard('api')->user();

        $userLat = isset($request['lat']) ? floatval($request['lat']) : ($user?->latitude !== null ? floatval($user?->latitude) : null);
        $userLng = isset($request['lng']) ? floatval($request['lng']) : ($user?->longitude !== null ? floatval($user?->longitude) : null);
        $distanceKm = $request['area'] ? floatval($request['area']) : 2;

        $categoriesSlugs = isset($request['categories']) ? explode(',', $request['categories']) : [];
        $subcategoriesSlugs = isset($request['subcategories']) ? explode(',', $request['subcategories']) : [];

        $categoriesIds = Category::whereIn('slug', $categoriesSlugs)->pluck('id')->toArray();
        $subcategoriesIds = Category::whereIn('slug', $subcategoriesSlugs)->pluck('id')->toArray();


        $query = Place::where('status', 1)->selectRaw(
            'places.*, ( 6371 * acos( cos( radians(?) ) * cos( radians( places.latitude ) ) * cos( radians( places.longitude ) - radians(?) ) + sin( radians(?) ) * sin( radians( places.latitude ) ) ) ) AS distance',
            [$userLat, $userLng, $userLat]
        )->having('distance', '<=', $distanceKm);

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


        $count = (clone $query)->count();

        // Then apply limit only if needed
        if ($count > 100) {
            $places = $query->orderBy('distance')->limit(100)->get();
        } else {
            $places = $query->orderBy('distance')->get();
        }

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
        $user = Auth::guard('api')->user();
        $notifications = DatabaseNotification::where('type', '!=', 'Filament\Notifications\DatabaseNotification')
            ->where('notifiable_type', 'App\Models\User')
            ->where('notifiable_id', $user->id)
            ->orderBy('created_at', 'desc')->get();
        return  UserNotificationResource::collection($notifications);
    }

    public function readNotification($id)
    {
        $user = Auth::guard('api')->user();
        $notification = $user->notifications()->where('id', $id)->first();
        $notification->markAsRead();
        return $notification;
    }

    public function unreadNotifications()
    {
        $user = Auth::guard('api')->user();
        $notifications = $user->notifications()->where('type', '!=', 'Filament\Notifications\DatabaseNotification')->where('read_at', null)->count();
        return ['count' => $notifications];
    }

    public function deleteNotifications($id)
    {
        $user = Auth::guard('api')->user();
        $notification = $user->notifications()->where('id', $id)->first();
        $notification->delete();
    }

    public function warning($data)
    {
        $gallery = $data['images'];
        $user = User::findBySlug($data['user_slug']);
        $userId = $user->id;
        $warning = new Warning();
        $warning->reporter_id = Auth::guard('api')->user()?->id;
        $warning->reported_id = $userId;
        $warning->reason = $data['reason'];
        $warning->save();

        if ($gallery !== null) {
            foreach ($gallery as $image) {
                $extension = pathinfo($image->getClientOriginalName(), PATHINFO_EXTENSION);
                $filename = Str::random(10) . '_' . time() . '.' . $extension;
                $warning->addMedia($image)->usingFileName($filename)->toMediaCollection('warning_app');
            }
        }

        adminNotification(
            'New Report',
            'A new report has been create by ' . Auth::guard('api')->user()->username,
            ['action' => 'view_report', 'action_label' => 'View Report', 'action_url' => route('filament.admin.resources.warnings.view', $warning)]
        );
    }
}

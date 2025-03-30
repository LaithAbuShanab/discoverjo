<?php

namespace App\Repositories\Api\User;

use App\Http\Resources\FeaturesResource;
use App\Http\Resources\UserFavoriteResource;
use App\Http\Resources\UserFavoriteSearchResource;
use App\Interfaces\Gateways\Api\User\FavoriteApiRepositoryInterface;
use App\Models\Feature;
use App\Models\User;
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
            ->leftJoin('guide_trips', function ($join) {
                $join->on('favorables.favorable_id', '=', 'guide_trips.id')
                    ->where('favorables.favorable_type', '=', 'App\Models\GuideTrip');
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
                    ->orWhere('posts.content', 'like', '%' . $searchTerm . '%')
                    ->orWhereRaw('LOWER(JSON_UNQUOTE(JSON_EXTRACT(guide_trips.name, "$.en"))) like ?', ['%' . strtolower($searchTerm) . '%'])
                    ->orWhereRaw('LOWER(JSON_UNQUOTE(JSON_EXTRACT(guide_trips.name, "$.ar"))) like ?', ['%' . strtolower($searchTerm) . '%']);
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

}

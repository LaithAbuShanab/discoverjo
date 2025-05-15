<?php

namespace App\Repositories\Api\User;

use App\Http\Resources\PlaceResource;
use App\Http\Resources\SingleSubCategoryResource;
use App\Interfaces\Gateways\Api\User\SubCategoryApiRepositoryInterface;
use App\Models\Category;
use App\Models\Place;
use App\Models\SubCategory;
use Illuminate\Support\Facades\Auth;


class EloquentSubCategoryApiRepository implements SubCategoryApiRepositoryInterface
{
    public function singleSubCategory($slug)
    {
        // Fetch the child category by ID and ensure it's not a main category
        $subCategory = Category::where('slug', $slug)
            ->whereNotNull('parent_id')
            ->with('places')
            ->firstOrFail();

        $id = $subCategory->id;

        $user = Auth::guard('api')->user();

        $userLat = request()->lat ?? ($user && $user->latitude ? $user->latitude : null);
        $userLng = request()->lng ?? ($user && $user->longitude ? $user->longitude : null);

        // Set the number of places per page
        $perPage = config('app.pagination_per_page'); // Adjust this number as needed

        // Retrieve places associated with the specific child category
        $places = Place::selectRaw('places.*, ( 6371 * acos( cos( radians(?) ) * cos( radians( places.latitude ) ) * cos( radians( places.longitude ) - radians(?) ) + sin( radians(?) ) * sin( radians( places.latitude ) ) ) ) AS distance', [$userLat, $userLng, $userLat])
            ->where('status',1)
            ->whereIn('id', function ($query) use ($id) {
                $query->select('place_id')
                    ->from('place_categories')
                    ->where('category_id', $id);
            })
            ->orderBy('distance') // Sort by distance
            ->paginate($perPage);

        // Convert pagination result to array and include pagination metadata
        $placesArray = $places->toArray();
        $pagination = [
            'next_page_url' => $placesArray['next_page_url'] ? $placesArray['next_page_url'] . '&lat=' . $userLat . '&lng=' . $userLng : null,
            'prev_page_url' => $placesArray['prev_page_url'] ? $placesArray['prev_page_url'] . '&lat=' . $userLat . '&lng=' . $userLng : null,
            'total' => $placesArray['total'],
        ];
        activityLog('subcategory',$subCategory,'the user viewed Subcategory','view');

        // Return the child category details and associated places with pagination
        return [
            'sub_category' => new SingleSubCategoryResource($subCategory),
            'places' => PlaceResource::collection($places),
            'pagination' => $pagination
        ];
    }
}

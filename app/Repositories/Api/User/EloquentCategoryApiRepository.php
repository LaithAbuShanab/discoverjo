<?php

namespace App\Repositories\Api\User;

use App\Http\Resources\AllCategoriesResource;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\PlaceResource;
use App\Interfaces\Gateways\Api\User\CategoryApiRepositoryInterface;
use App\Models\Category;
use App\Models\Place;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;


class EloquentCategoryApiRepository implements CategoryApiRepositoryInterface
{
    public function getAllCategories()
    {
        $eloquentCategories = Category::whereNull('parent_id')->orderBy('priority')->get();
        return AllCategoriesResource::collection($eloquentCategories);
    }

    public function shuffleAllCategories()
    {
        $eloquentCategories = Category::whereNull('parent_id')->get();
        $shuffledCategories = $eloquentCategories->shuffle();
        return AllCategoriesResource::collection($shuffledCategories);
    }

    public function allPlacesByCategory($slug)
    {
        $category = Category::with('children')->where('slug', $slug)->first();

        // Retrieve all subcategories
        $allSubcategories = $category->children()->whereHas('places')->get();

        $userLat = request()->lat ? request()->lat : null;
        $userLng = request()->lng ? request()->lng : null;

        // Set the number of places per page
        $perPage = 20; // You can adjust this number based on your requirements

        // Retrieve places associated with the category and its children
        $places = Place::selectRaw('places.*, ( 6371 * acos( cos( radians(?) ) * cos( radians( places.latitude ) ) * cos( radians( places.longitude ) - radians(?) ) + sin( radians(?) ) * sin( radians( places.latitude ) ) ) ) AS distance', [$userLat, $userLng, $userLat])
            ->whereIn('id', function ($query) use ($category) {
                $query->select('place_id')
                    ->from('place_categories')
                    ->whereIn('category_id', function ($subQuery) use ($category) {
                        $subQuery->select('id')
                            ->from('categories')
                            ->where('parent_id', $category->id)
                            ->orWhere('id', $category->id); // Include the main category itself
                    });
            })
            ->orderBy('distance') // Sort by distance
            ->paginate($perPage);


        $placesArray = $places->toArray();
        if ($userLat && $userLng) {
            $parameterNext = $placesArray['next_page_url'] ? $placesArray['next_page_url'] . '&lat=' . $userLat . "&lng=" . $userLng : $placesArray['next_page_url'];
            $parameterPrevious = $placesArray['prev_page_url'] ? $placesArray['prev_page_url'] . '&lat=' . $userLat . "&lng=" . $userLng : $placesArray['prev_page_url'];
        } else {
            $parameterNext = $placesArray['next_page_url'] ? $placesArray['next_page_url'] . '&lat=' . $userLat . "&lng=" . $userLng : null;
            $parameterPrevious = $placesArray['prev_page_url'] ? $placesArray['prev_page_url'] . '&lat=' . $userLat . "&lng=" . $userLng : null;
        }

        // Convert pagination result to array and include pagination metadata

        $pagination = [
            'next_page_url' => $parameterNext,
            'prev_page_url' => $parameterPrevious,
            'total' => $placesArray['total'],
        ];

        // Pass user coordinates to the PlaceResource collection
        return [
            'category' => new AllCategoriesResource($category),
            'sub_categories' => CategoryResource::collection($allSubcategories),
            'places' => PlaceResource::collection($places),
            'pagination' => $pagination
        ];
    }

    public function allSubcategories($data)
    {
        $subcategories = Category::whereIn('slug', $data)->with('children')->get();
        $allChildren = $subcategories->pluck('children')->flatten();
        return CategoryResource::collection($allChildren);
    }

    public function search($query)
    {
        $categories = Category::where(function ($queryBuilder) use ($query) {
            $queryBuilder->whereRaw('LOWER(JSON_UNQUOTE(JSON_EXTRACT(name, "$.en"))) like ?', ['%' . strtolower($query) . '%'])
                ->orWhereRaw('LOWER(JSON_UNQUOTE(JSON_EXTRACT(name, "$.ar"))) like ?', ['%' . strtolower($query) . '%']);
        })->whereNull('parent_id')->get();

        return new ResourceCollection(AllCategoriesResource::collection($categories));
    }
}

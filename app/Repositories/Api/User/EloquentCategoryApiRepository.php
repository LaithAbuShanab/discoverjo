<?php

namespace App\Repositories\Api\User;

use App\Http\Resources\AllCategoriesResource;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\PlaceResource;
use App\Interfaces\Gateways\Api\User\CategoryApiRepositoryInterface;
use App\Models\Category;
use App\Models\Place;

class EloquentCategoryApiRepository implements CategoryApiRepositoryInterface
{
    public function getAllCategories()
    {
        $eloquentCategories = Category::whereNull('parent_id')->orderBy('priority')->get();
        return AllCategoriesResource::collection($eloquentCategories);
    }

    public function allSubcategories($data)
    {
        $subcategories = Category::whereIn('slug', $data)->with('children')->get();
        $allChildren = $subcategories->pluck('children')->flatten();
        return CategoryResource::collection($allChildren);
    }

    public function shuffleAllCategories()
    {
        $eloquentCategories = Category::whereNull('parent_id')->get();
        $shuffledCategories = $eloquentCategories->shuffle();
        return AllCategoriesResource::collection($shuffledCategories);
    }

    public function allPlacesByCategory($slug)
    {
        $perPage = config('app.pagination_per_page');
        $category = Category::with('children')->where('slug', $slug)->first();

        $allSubcategories = $category->children()->whereHas('places')->get();

        $userLat = request()->lat ? request()->lat : null;
        $userLng = request()->lng ? request()->lng : null;

        $places = Place::selectRaw(
            'places.*, ( 6371 * acos( cos( radians(?) ) * cos( radians( places.latitude ) ) * cos( radians( places.longitude ) - radians(?) ) + sin( radians(?) ) * sin( radians( places.latitude ) ) ) ) AS distance',
            [$userLat, $userLng, $userLat]
        )
            ->where('status', 1)
            ->whereHas('categories', function ($query) use ($category) {
                $query->where('category_id', $category->id)
                    ->orWhereIn('category_id', $category->children->pluck('id'));
            })
            ->orderBy('distance')
            ->paginate($perPage)
            ->appends(['lat' => $userLat, 'lng' => $userLng]);


        $placesArray = $places->toArray();
        if ($userLat && $userLng) {
            $parameterNext = $placesArray['next_page_url'] ? $placesArray['next_page_url'] . '&lat=' . $userLat . "&lng=" . $userLng : $placesArray['next_page_url'];
            $parameterPrevious = $placesArray['prev_page_url'] ? $placesArray['prev_page_url'] . '&lat=' . $userLat . "&lng=" . $userLng : $placesArray['prev_page_url'];
        } else {
            $parameterNext = $placesArray['next_page_url'] ? $placesArray['next_page_url'] . '&lat=' . $userLat . "&lng=" . $userLng : null;
            $parameterPrevious = $placesArray['prev_page_url'] ? $placesArray['prev_page_url'] . '&lat=' . $userLat . "&lng=" . $userLng : null;
        }

        $pagination = [
            'next_page_url' => $parameterNext,
            'prev_page_url' => $parameterPrevious,
            'total' => $placesArray['total'],
        ];

        return [
            'category' => new AllCategoriesResource($category),
            'sub_categories' => CategoryResource::collection($allSubcategories),
            'places' => PlaceResource::collection($places),
            'pagination' => $pagination
        ];
    }

    public function search($query)
    {
        $categories = Category::where(function ($queryBuilder) use ($query) {
            $queryBuilder->whereRaw('LOWER(JSON_UNQUOTE(JSON_EXTRACT(name, "$.en"))) like ?', ['%' . strtolower($query) . '%'])
                ->orWhereRaw('LOWER(JSON_UNQUOTE(JSON_EXTRACT(name, "$.ar"))) like ?', ['%' . strtolower($query) . '%']);
        })->whereNull('parent_id')->get();

        return AllCategoriesResource::collection($categories);
    }
}

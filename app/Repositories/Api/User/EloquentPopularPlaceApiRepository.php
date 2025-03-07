<?php

namespace App\Repositories\Api\User;

use App\Http\Resources\CategoryResource;
use App\Http\Resources\PopularPlaceResource;
use App\Http\Resources\SinglePlaceResource;
use App\Interfaces\Gateways\Api\User\PlaceApiRepositoryInterface;
use App\Interfaces\Gateways\Api\User\PopularPlaceApiRepositoryInterface;
use App\Models\Category;
use App\Models\Place;
use App\Models\PopularPlace;


class EloquentPopularPlaceApiRepository implements PopularPlaceApiRepositoryInterface
{



    public function popularPlaces()
    {
        $popularPlace = PopularPlace::with('place')->get();
        $shuffledPopularPlaces = $popularPlace->shuffle();
        return new PopularPlaceResource($shuffledPopularPlaces);
    }

    public function search($query)
    {
        $places = PopularPlace::whereHas('place', function ($queryBuilder) use ($query) {
            $queryBuilder->where(function ($queryBuilder) use ($query) {
                $queryBuilder->whereRaw('LOWER(JSON_UNQUOTE(JSON_EXTRACT(places.name, "$.en"))) like ?', ['%' . strtolower($query) . '%'])
                    ->orWhereRaw('LOWER(JSON_UNQUOTE(JSON_EXTRACT(places.name, "$.ar"))) like ?', ['%' . strtolower($query) . '%'])
                    ->orWhereRaw('LOWER(JSON_UNQUOTE(JSON_EXTRACT(places.description, "$.en"))) like ?', ['%' . strtolower($query) . '%'])
                    ->orWhereRaw('LOWER(JSON_UNQUOTE(JSON_EXTRACT(places.description, "$.ar"))) like ?', ['%' . strtolower($query) . '%']);
            });
        })->get();

        return new PopularPlaceResource($places);
    }

}

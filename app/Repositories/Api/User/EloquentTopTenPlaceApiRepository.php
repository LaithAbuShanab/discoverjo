<?php

namespace App\Repositories\Api\User;

use App\Http\Resources\TopTenPlaceResource;
use App\Interfaces\Gateways\Api\User\TopTenPlaceApiRepositoryInterface;
use App\Models\TopTen;


class EloquentTopTenPlaceApiRepository implements TopTenPlaceApiRepositoryInterface
{



    public function topTenPlaces()
    {
        $topTenPlaces = TopTen::whereHas('place', fn($query) => $query->where('status', 1))->get();
        $shuffledTopTenPlaces = $topTenPlaces->shuffle();
        activityLog('top ten', $topTenPlaces->first(), 'the user view top ten list', 'search');
        return new TopTenPlaceResource($shuffledTopTenPlaces);
    }
    public function search($query)
    {
        $places = TopTen::whereHas('place', function ($queryBuilder) use ($query) {
            $queryBuilder->where('status', 1)
            ->where(function ($queryBuilder) use ($query) {
                $queryBuilder->whereRaw('LOWER(JSON_UNQUOTE(JSON_EXTRACT(places.name, "$.en"))) like ?', ['%' . strtolower($query) . '%'])
                    ->orWhereRaw('LOWER(JSON_UNQUOTE(JSON_EXTRACT(places.name, "$.ar"))) like ?', ['%' . strtolower($query) . '%'])
                    ->orWhereRaw('LOWER(JSON_UNQUOTE(JSON_EXTRACT(places.description, "$.en"))) like ?', ['%' . strtolower($query) . '%'])
                    ->orWhereRaw('LOWER(JSON_UNQUOTE(JSON_EXTRACT(places.description, "$.ar"))) like ?', ['%' . strtolower($query) . '%']);
            });
        })->get();

        if($query) {
            activityLog('top ten', $places->first(), $query, 'search');
        }
        return new TopTenPlaceResource($places);
    }


}

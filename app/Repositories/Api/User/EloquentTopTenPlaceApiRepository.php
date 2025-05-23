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
        activityLog('top ten', $topTenPlaces->first(), 'the user view top ten list', 'view');
        return TopTenPlaceResource::collection($shuffledTopTenPlaces);
    }
    public function search($data)
    {
        $query = $data['query'];
        $places = TopTen::with('place')->whereHas('place', function ($queryBuilder) use ($query) {
            $queryBuilder->where('status', 1)
                ->when($query, function ($q) use ($query) {
                    $q->where(function ($q2) use ($query) {
                        $q2->where('name_en', 'like', '%' . $query . '%')
                            ->orWhere('name_ar', 'like', '%' . $query . '%');
                    });
                });
        })->get();
        if($query) {
            activityLog('top ten', $places->first(), $query, 'search');
        }
        return  TopTenPlaceResource::collection($places);
    }


}

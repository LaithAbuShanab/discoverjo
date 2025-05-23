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
use Illuminate\Support\Facades\Auth;


class EloquentPopularPlaceApiRepository implements PopularPlaceApiRepositoryInterface
{



    public function popularPlaces()
    {
        $popularPlaces = PopularPlace::whereHas('place', fn($query) => $query->where('status', 1))->get();
        $shuffledPopularPlaces = $popularPlaces->shuffle();
        return PopularPlaceResource::collection($shuffledPopularPlaces);
    }


    public function search($data)
    {

        $query= $data['query'];
        $places = PopularPlace::with('place')->whereHas('place', function ($queryBuilder) use ($query) {
            $queryBuilder->where('status', 1)
                ->when($query, function ($q) use ($query) {
                    $q->where(function ($q2) use ($query) {
                        $q2->where('name_en', 'like', '%' . $query . '%')
                            ->orWhere('name_ar', 'like', '%' . $query . '%');
                    });
                });
        })->get();

        if($query) {
            activityLog('popular place', $places->first(), $query, 'search');
        }
        return  PopularPlaceResource::collection($places);
    }

}

<?php

namespace App\Repositories\Api\User;

use App\Http\Resources\AllCategoriesResource;
use App\Http\Resources\RegionResource;
use App\Interfaces\Gateways\Api\User\RegionsApiRepositoryInterface;
use App\Models\Region;

class EloquentRegionsApiRepository implements RegionsApiRepositoryInterface
{

    public function allRegions()
    {
        $eloquentRegions = Region::all();
        return RegionResource::collection($eloquentRegions);
    }
}

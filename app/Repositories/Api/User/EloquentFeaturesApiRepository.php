<?php

namespace App\Repositories\Api\User;

use App\Http\Resources\FeaturesResource;
use App\Interfaces\Gateways\Api\User\FeaturesApiRepositoryInterface;
use App\Models\Feature;

class EloquentFeaturesApiRepository implements FeaturesApiRepositoryInterface
{

    public function allFeatures()
    {
        $eloquentFeatures = Feature::all();
        return FeaturesResource::collection($eloquentFeatures);
    }
}

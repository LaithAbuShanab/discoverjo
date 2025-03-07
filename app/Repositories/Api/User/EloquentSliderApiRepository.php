<?php

namespace App\Repositories\Api\User;

use App\Http\Resources\SliderResource;
use App\Interfaces\Gateways\Api\User\SliderApiRepositoryInterface;
use App\Models\Slider;
use Illuminate\Http\Resources\Json\ResourceCollection;


class EloquentSliderApiRepository implements SliderApiRepositoryInterface
{
    public function allOnboardings()
    {
        $eloquentSliders = Slider::where('type','onboarding')->where('status',1)->orderBy('priority')->take(3)->get();
        return SliderResource::collection($eloquentSliders) ;
    }


}

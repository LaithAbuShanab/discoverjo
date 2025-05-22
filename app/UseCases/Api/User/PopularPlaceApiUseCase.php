<?php

namespace App\UseCases\Api\User;

use App\Interfaces\Gateways\Api\User\PlaceApiRepositoryInterface;
use App\Interfaces\Gateways\Api\User\PopularPlaceApiRepositoryInterface;

class PopularPlaceApiUseCase
{
    protected $popularPlaceApiRepository;

    public function __construct(PopularPlaceApiRepositoryInterface $popularPlaceApiRepository)
    {
        $this->popularPlaceApiRepository = $popularPlaceApiRepository;
    }



    public function popularPlaces()
    {
        return $this->popularPlaceApiRepository->popularPlaces();
    }
    public function search($query)
    {
        return $this->popularPlaceApiRepository->search($query);
    }


}

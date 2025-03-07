<?php

namespace App\UseCases\Api\User;

use App\Interfaces\Gateways\Api\User\TopTenPlaceApiRepositoryInterface;

class TopTenPlaceApiUseCase
{
    protected $topTenPlaceApiRepository;

    public function __construct(TopTenPlaceApiRepositoryInterface $topTenPlaceApiRepository)
    {
        $this->topTenPlaceApiRepository = $topTenPlaceApiRepository;
    }



    public function topTenPlaces()
    {
        return $this->topTenPlaceApiRepository->topTenPlaces();
    }

    public function search($query)
    {
        return $this->topTenPlaceApiRepository->search($query);
    }


}

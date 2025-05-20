<?php

namespace App\UseCases\Api\User;

use App\Interfaces\Gateways\Api\User\PlaceApiRepositoryInterface;

class PlaceApiUseCase
{
    protected $placeApiRepository;
    public function __construct( PlaceApiRepositoryInterface $placeApiRepository)
    {
        $this->placeApiRepository = $placeApiRepository;
    }

    public function singlePlace($slug)
    {
        return $this->placeApiRepository->singlePlace($slug);
    }

    public function createVisitedPlace($slug)
    {
        return $this->placeApiRepository->createVisitedPlace($slug);
    }

    public function deleteVisitedPlace($slug)
    {
        return $this->placeApiRepository->deleteVisitedPlace($slug);
    }

    public function search($data){
        return $this->placeApiRepository->search($data);
    }
    public function filter($data){
        return $this->placeApiRepository->filter($data);
    }
    public function allSearch($data){
            return $this->placeApiRepository->allSearch($data);
    }

}

<?php

namespace App\UseCases\Api\User;

use App\Interfaces\Gateways\Api\User\PlaceApiRepositoryInterface;

class PlaceApiUseCase
{
    public function __construct(protected PlaceApiRepositoryInterface $placeApiRepository)
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

    public function addReview($data)
    {
        return $this->placeApiRepository->addReview($data);
    }

    public function updateReview($data)
    {
        return $this->placeApiRepository->updateReview($data);
    }

    public function deleteReview($id)
    {
        return $this->placeApiRepository->deleteReview($id);
    }

    public function reviewsLike($data)
    {
        return $this->placeApiRepository->reviewsLike($data);
    }

    public function search($query){
        return $this->placeApiRepository->search($query);
    }
    public function filter($data){
        return $this->placeApiRepository->filter($data);
    }
    public function allSearch($data){
            return $this->placeApiRepository->allSearch($data);
    }

}

<?php

namespace App\UseCases\Api\User;

use App\Interfaces\Gateways\Api\User\PlaceApiRepositoryInterface;
use App\Models\Place;
use Illuminate\Support\Facades\Auth;

class PlaceApiUseCase
{
    protected $placeApiRepository;

    public function __construct(PlaceApiRepositoryInterface $placeApiRepository)
    {
        $this->placeApiRepository = $placeApiRepository;
    }

    public function singlePlace($slug)
    {
        return $this->placeApiRepository->singlePlace($slug);
    }

    public function createFavoritePlace($slug)
    {
        $placeId = Place::findBySlug($slug)?->id;
        $user_id = Auth::guard('api')->user()->id;
        $data = [
            'place_id' => $placeId,
            'user_id' => $user_id
        ];
        return $this->placeApiRepository->createFavoritePlace($data);
    }

    public function deleteFavoritePlace($id)
    {
        return $this->placeApiRepository->deleteFavoritePlace($id);
    }

    public function createVisitedPlace($id)
    {
        $user_id = Auth::guard('api')->user()->id;
        $data = [
            'place_id' => $id,
            'user_id' => $user_id
        ];
        return $this->placeApiRepository->createVisitedPlace($data);
    }

    public function deleteVisitedPlace($id)
    {
        return $this->placeApiRepository->deleteVisitedPlace($id);
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

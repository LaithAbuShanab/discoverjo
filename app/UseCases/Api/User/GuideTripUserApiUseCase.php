<?php

namespace App\UseCases\Api\User;

use App\Interfaces\Gateways\Api\User\GuideTripUserApiRepositoryInterface;
use Illuminate\Support\Facades\Auth;

class GuideTripUserApiUseCase
{
    protected $guideTripUserApiRepository;

    public function __construct(GuideTripUserApiRepositoryInterface $guideTripUserApiRepository)
    {
        $this->guideTripUserApiRepository = $guideTripUserApiRepository;
    }



    public function allUsersForGuideTrip()
    {
        return $this->guideTripUserApiRepository->allUsersForGuideTrip();
    }

    public function storeSubscriberInTrip($data)
    {
        return $this->guideTripUserApiRepository->storeSubscriberInTrip($data);
    }
    public function updateSubscriberInTrip($data)
    {
        return $this->guideTripUserApiRepository->updateSubscriberInTrip($data);
    }
    public function deleteSubscriberInTrip($id)
    {
        return $this->guideTripUserApiRepository->deleteSubscriberInTrip($id);
    }public function allSubscription($id)
    {
        return $this->guideTripUserApiRepository->allSubscription($id);
    }

    public function favorite($id)
    {
        return $this->guideTripUserApiRepository->favorite($id);
    }

    public function deleteFavorite($id)
    {
        return $this->guideTripUserApiRepository->deleteFavorite($id);
    }
    public function addReview($data)
    {
        return $this->guideTripUserApiRepository->addReview($data);
    }

    public function updateReview($data)
    {
        return $this->guideTripUserApiRepository->updateReview($data);
    }

    public function deleteReview($id)
    {
        return $this->guideTripUserApiRepository->deleteReview($id);
    }

    public function reviewsLike($data)
    {
        return $this->guideTripUserApiRepository->reviewsLike($data);
    }

    public function search($query){
        return $this->guideTripUserApiRepository->search($query);
    }

}

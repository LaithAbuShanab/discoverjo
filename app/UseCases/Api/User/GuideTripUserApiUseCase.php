<?php

namespace App\UseCases\Api\User;

use App\Interfaces\Gateways\Api\User\GuideTripUserApiRepositoryInterface;

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

    public function updateSingleSubscription($data)
    {
        return $this->guideTripUserApiRepository->updateSingleSubscription($data);
    }

    public function storeSingleSubscription($data)
    {
        return $this->guideTripUserApiRepository->storeSingleSubscription($data);
    }

    public function singleSubscription($id)
    {
        return $this->guideTripUserApiRepository->singleSubscription($id);
    }

    public function deleteSingleSubscription($id)
    {
        return $this->guideTripUserApiRepository->deleteSingleSubscription($id);
    }

    public function deleteSubscriberInTrip($slug)
    {
        return $this->guideTripUserApiRepository->deleteSubscriberInTrip($slug);
    }

    public function allSubscription($slug)
    {
        return $this->guideTripUserApiRepository->allSubscription($slug);
    }

    public function search($query)
    {
        return $this->guideTripUserApiRepository->search($query);
    }
}

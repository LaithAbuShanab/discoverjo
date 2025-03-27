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

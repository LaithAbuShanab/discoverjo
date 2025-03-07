<?php

namespace App\UseCases\Api\User;

use App\Interfaces\Gateways\Api\User\TripApiRepositoryInterface;

class TripApiUseCase
{
    protected $tripApiRepository;

    public function __construct(TripApiRepositoryInterface $tripApiRepository)
    {
        $this->tripApiRepository = $tripApiRepository;
    }

    public function trips()
    {
        return $this->tripApiRepository->trips();
    }

    public function allTrips()
    {
        return $this->tripApiRepository->allTrips();
    }

    public function invitationTrips()
    {
        return $this->tripApiRepository->invitationTrips();
    }

    public function changeStatusInvitation($request)
    {
        return $this->tripApiRepository->changeStatusInvitation($request);
    }

    public function tags()
    {
        return $this->tripApiRepository->tags();
    }

    public function createTrip($request)
    {
        return $this->tripApiRepository->createTrip($request);
    }

    public function joinTrip($trip_id)
    {
        return $this->tripApiRepository->joinTrip($trip_id);
    }

    public function cancelJoinTrip($trip_id, $request)
    {
        return $this->tripApiRepository->cancelJoinTrip($trip_id, $request);
    }

    public function privateTrips()
    {
        return $this->tripApiRepository->privateTrips();
    }

    public function tripDetails($trip_id)
    {
        return $this->tripApiRepository->tripDetails($trip_id);
    }

    public function changeStatus($request)
    {
        return $this->tripApiRepository->changeStatus($request);
    }

    public function favorite($id)
    {
        return $this->tripApiRepository->favorite($id);
    }

    public function deleteFavorite($id)
    {
        return $this->tripApiRepository->deleteFavorite($id);
    }

    public function addReview($data)
    {

        return $this->tripApiRepository->addReview($data);
    }

    public function updateReview($data)
    {
        return $this->tripApiRepository->updateReview($data);
    }
    public function deleteReview($id)
    {
        return $this->tripApiRepository->deleteReview($id);
    }

    public function allReviews($id)
    {
        return $this->tripApiRepository->allReviews($id);
    }

    public function reviewsLike($data)
    {
        return $this->tripApiRepository->reviewsLike($data);
    }

    public function remove($trip_id)
    {
        return $this->tripApiRepository->remove($trip_id);
    }

    public function update($data)
    {
        return $this->tripApiRepository->update($data);
    }
    public function search($query)
    {
        return $this->tripApiRepository->search($query);
    }

    public function removeUser($data)
    {
        return $this->tripApiRepository->removeUser($data);
    }
}
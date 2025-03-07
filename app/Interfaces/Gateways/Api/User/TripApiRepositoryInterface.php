<?php

namespace App\Interfaces\Gateways\Api\User;


interface TripApiRepositoryInterface
{
    public function trips();

    public function allTrips();

    public function invitationTrips();

    public function changeStatusInvitation($data);

    public function privateTrips();

    public function tripDetails($trip_id);

    public function tags();

    public function createTrip($data);

    public function joinTrip($trip_id);

    public function cancelJoinTrip($trip_id, $request);

    public function changeStatus($data);

    public function favorite($id);

    public function deleteFavorite($id);

    public function addReview($data);

    public function updateReview($data);

    public function deleteReview($id);

    public function allReviews($id);

    public function reviewsLike($data);

    public function remove($trip_id);

    public function update($data);

    public function search($query);

    public function removeUser($request);
}
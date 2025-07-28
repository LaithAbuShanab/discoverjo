<?php

namespace App\Interfaces\Gateways\Api\User;


interface TripApiRepositoryInterface
{
    public function trips();

    public function allTrips();

    public function changeStatus($data);

    public function invitationTrips();

    public function invitationCount();

    public function changeStatusInvitation($data);

    public function privateTrips();

    public function tripDetails($slug);

    public function tags();

    public function createTrip($data);

    public function joinTrip($slug);

    public function cancelJoinTrip($slug, $request);

    public function favorite($id);

    public function deleteFavorite($id);

    public function addReview($data);

    public function updateReview($data);

    public function deleteReview($id);

    public function allReviews($id);

    public function reviewsLike($data);

    public function remove($slug);

    public function update($data);

    public function search($query);

    public function removeUser($request);

    public function dateTrips($date);
}

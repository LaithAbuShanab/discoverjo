<?php

namespace App\Interfaces\Gateways\Api\User;


interface VolunteeringApiRepositoryInterface
{
    public function getAllVolunteerings();
    public function activeVolunteerings();
    public function volunteering($slug);
    public function dateVolunteerings($date);
    public function createInterestVolunteering($slug);
    public function disinterestVolunteering($slug);
    public function favorite($id);
    public function deleteFavorite($id);
    public function addReview($data);
    public function updateReview($data);
    public function deleteReview($id);
    public function reviewsLike($request);
    public function search($query);
    public function interestedList($id);
}

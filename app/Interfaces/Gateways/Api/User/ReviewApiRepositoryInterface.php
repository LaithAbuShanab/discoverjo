<?php

namespace App\Interfaces\Gateways\Api\User;


interface ReviewApiRepositoryInterface
{
    public function allReviews($data);
    public function addReview($data);
    public function updateReview($data);
    public function deleteReview($data);
    public function reviewsLike($data);

}

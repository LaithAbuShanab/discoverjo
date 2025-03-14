<?php

namespace App\UseCases\Api\User;

use App\Interfaces\Gateways\Api\User\ReviewApiRepositoryInterface;
use Illuminate\Support\Facades\Auth;

class ReviewApiUseCase
{
    protected $reviewApiRepository;

    public function __construct(ReviewApiRepositoryInterface $reviewApiRepository)
    {
        $this->reviewApiRepository = $reviewApiRepository;
    }

    public function allReviews($data)
    {
        return $this->reviewApiRepository->allReviews($data);
    }

    public function addReview($data)
    {
        return $this->reviewApiRepository->addReview($data);
    }

    public function updateReview($data)
    {
        return $this->reviewApiRepository->updateReview($data);
    }

    public function deleteReview($query){
        return $this->reviewApiRepository->deleteReview($query);
    }

    public function reviewsLike($data){
        return $this->reviewApiRepository->reviewsLike($data);
    }
}

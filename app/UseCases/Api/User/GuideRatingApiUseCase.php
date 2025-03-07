<?php

namespace App\UseCases\Api\User;


use App\Interfaces\Gateways\Api\User\GuideRatingApiRepositoryInterface;
use Illuminate\Support\Facades\Auth;

class GuideRatingApiUseCase
{
    protected $guideRatingRepository;

    public function __construct(GuideRatingApiRepositoryInterface $guideRatingRepository)
    {
        $this->guideRatingRepository = $guideRatingRepository;
    }

    public function createGuideRating($data)
    {
        return $this->guideRatingRepository->createGuideRating([
            'user_id'=>Auth::guard('api')->user()->id,
            'guide_id'=>$data['guide_id'],
            'rating'=>$data['rating']
        ]);

    }

    public function updateGuideRating($data)
    {
        return $this->guideRatingRepository->updateGuideRating([
            'user_id'=>Auth::guard('api')->user()->id,
            'guide_id'=>$data['guide_id'],
            'rating'=>$data['rating']
        ]);
    }

    public function deleteGuideRating($id)
    {
        return $this->guideRatingRepository->deleteGuideRating($id);
    }

    public function showGuideRating($id)
    {
        return $this->guideRatingRepository->showGuideRating($id);
    }





}

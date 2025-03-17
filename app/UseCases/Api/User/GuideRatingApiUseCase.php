<?php

namespace App\UseCases\Api\User;


use App\Interfaces\Gateways\Api\User\GuideRatingApiRepositoryInterface;
use App\Models\User;
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
        $guide = User::findBySlug($data['guide_slug']);
        return $this->guideRatingRepository->createGuideRating([
            'user_id'=>Auth::guard('api')->user()->id,
            'guide_id'=>$guide->id,
            'rating'=>$data['rating']
        ]);

    }

    public function updateGuideRating($data)
    {
        $guide = User::findBySlug($data['guide_slug']);
        return $this->guideRatingRepository->updateGuideRating([
            'user_id'=>Auth::guard('api')->user()->id,
            'guide_id'=>$guide->id,
            'rating'=>$data['rating']
        ]);
    }

    public function deleteGuideRating($slug)
    {
        return $this->guideRatingRepository->deleteGuideRating($slug);
    }

    public function showGuideRating($slug)
    {
        return $this->guideRatingRepository->showGuideRating($slug);
    }





}

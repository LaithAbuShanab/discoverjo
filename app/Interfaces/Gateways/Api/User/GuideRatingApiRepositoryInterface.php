<?php

namespace App\Interfaces\Gateways\Api\User;


interface GuideRatingApiRepositoryInterface
{
    public function createGuideRating($data);
    public function updateGuideRating($data);
    public function DeleteGuideRating($slug);
    public function showGuideRating($slug);


}

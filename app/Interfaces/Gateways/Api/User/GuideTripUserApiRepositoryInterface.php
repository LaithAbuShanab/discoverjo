<?php

namespace App\Interfaces\Gateways\Api\User;


use App\Models\GuideTrip;
use Illuminate\Support\Facades\DB;

interface GuideTripUserApiRepositoryInterface
{
    public function allUsersForGuideTrip();
    public function storeSubscriberInTrip($data);
    public function updateSubscriberInTrip($data);
    public function deleteSubscriberInTrip($id);
    public function allSubscription($id);
    public function favorite($id);

    public function deleteFavorite($id);
    public function addReview($data);

    public function updateReview($data);

    public function deleteReview($id);
    public function reviewsLike($request);
    public function search($query);

}

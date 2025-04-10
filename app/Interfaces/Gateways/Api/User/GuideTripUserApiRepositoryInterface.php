<?php

namespace App\Interfaces\Gateways\Api\User;


use App\Models\GuideTrip;
use Illuminate\Support\Facades\DB;

interface GuideTripUserApiRepositoryInterface
{
    public function allUsersForGuideTrip();
    public function storeSubscriberInTrip($data);
    public function updateSubscriberInTrip($data);
    public function deleteSubscriberInTrip($slug);
    public function allSubscription($slug);
    public function search($query);

}

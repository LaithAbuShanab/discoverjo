<?php

namespace App\Interfaces\Gateways\Api\User;

interface GuideTripUserApiRepositoryInterface
{
    public function allUsersForGuideTrip();
    public function storeSubscriberInTrip($data);
    //public function updateSubscriberInTrip($data);
    public function updateSingleSubscription($data);
    public function storeSingleSubscription($data);
    public function singleSubscription($id);
    public function deleteSingleSubscription($id);
    public function deleteSubscriberInTrip($slug);
    public function allSubscription($slug);
    public function search($query);
    public function dateGuideTrip($date);

}

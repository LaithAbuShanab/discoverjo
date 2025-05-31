<?php

namespace App\Interfaces\Gateways\Api\User;

interface GuideTripApiRepositoryInterface
{
    public function AllGuideTrip();
    public function allGuides();
    public function storeGuideTrip($mainData, $gallery,$activities, $priceInclude,$priceAge,$assembly,$requiredItem, $trail,$mainImage,$paymentMethods);
    public function updateGuideTrip($mainData, $slug, $gallery, $activities, $priceInclude, $priceAge, $assembly, $requiredItem, $trail,$mainImage,$paymentMethods);
    public function deleteGuideTrip($slug);
    public function deleteImage($id);
    public function showGuideTrip($slug);
    public function detailUpdate($slug);
    public function joinRequests($slug);
    public function changeJoinRequestStatus($request);
    public function tripsOfGuide($slug);


}

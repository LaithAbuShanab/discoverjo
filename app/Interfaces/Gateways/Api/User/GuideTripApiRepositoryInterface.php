<?php

namespace App\Interfaces\Gateways\Api\User;

interface GuideTripApiRepositoryInterface
{
    public function AllGuideTrip();
    public function allGuides();
    public function storeGuideTrip($mainData, $gallery,$activities, $priceInclude,$priceAge,$assembly,$requiredItem, $trail);
    public function updateGuideTrip($mainData, $slug, $gallery, $activities, $priceInclude, $priceAge, $assembly, $requiredItem, $trail);
    public function deleteGuideTrip($slug);
    public function deleteImage($id);
    public function showGuideTrip($slug);
    public function joinRequests($slug);
    public function changeJoinRequestStatus($request);


}

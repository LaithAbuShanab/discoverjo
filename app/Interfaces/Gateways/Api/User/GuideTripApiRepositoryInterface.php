<?php

namespace App\Interfaces\Gateways\Api\User;


use App\Models\GuideTrip;
use Illuminate\Support\Facades\DB;

interface GuideTripApiRepositoryInterface
{
    public function AllGuideTrip();
    public function allGuides();
    public function storeGuideTrip($mainData, $gallery,$activities, $priceInclude,$priceAge,$assembly,$requiredItem, $trail);

    public function updateGuideTrip($mainData, $id, $gallery, $activities, $priceInclude, $priceAge, $assembly, $requiredItem, $trail);

    public function deleteGuideTrip($id);
    public function deleteImage($id);
    public function showGuideTrip($slug);
    public function joinRequests($id);
    public function changeJoinRequestStatus($request);


}

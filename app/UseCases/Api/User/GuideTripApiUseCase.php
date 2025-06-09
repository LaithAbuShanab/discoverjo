<?php

namespace App\UseCases\Api\User;

use App\Interfaces\Gateways\Api\User\GuideTripApiRepositoryInterface;
use App\Models\Region;
use Illuminate\Support\Facades\Auth;

class GuideTripApiUseCase
{
    protected $guideTripApiRepository;

    public function __construct(GuideTripApiRepositoryInterface $guideTripApiRepository)
    {
        $this->guideTripApiRepository = $guideTripApiRepository;
    }



    public function AllGuideTrip()
    {
        return $this->guideTripApiRepository->AllGuideTrip();
    }

    public function allGuides()
    {
        return $this->guideTripApiRepository->allGuides();
    }

    public function showGuideTrip($slug)
    {
        return $this->guideTripApiRepository->showGuideTrip($slug);
    }

    public function detailUpdate($slug)
    {
        return $this->guideTripApiRepository->detailUpdate($slug);
    }

    public function storeGuideTrip($data)
    {

        $translator = ['en' => $data['name_en'], 'ar' => $data['name_ar']];
        $translatorDescription = ['en' => $data['description_en'], 'ar' => $data['description_ar']];
        $trailData =  $data['is_trail'] ? $data['trail'] : null;
        $regionId= Region::findBySlug($data['region'])->id;

        return $this->guideTripApiRepository->storeGuideTrip(
            [
                'name' => $translator,
                'guide_id' => Auth::guard('api')->user()->id,
                'description' => $translatorDescription,
                'main_price' => $data['main_price'],
                'start_datetime' => $data['start_datetime'],
                'end_datetime' => $data['end_datetime'],
                'max_attendance' => $data['max_attendance'],
                'region_id' => $regionId,
            ],

            isset($data['gallery']) ? $data['gallery'] : null,
            json_decode($data['activities']),
            json_decode($data['price_include']),
            isset($data['price_age']) ? json_decode($data['price_age']) : null,
            json_decode($data['assembly']),
            isset($data['required_items']) ? json_decode($data['required_items']) : null,

            json_decode($trailData),
            isset($data['main_image']) ? $data['main_image'] : null,
            json_decode($data['payment_method']),
        );
    }


    public function updateGuideTrip($data, $slug)
    {
        $translator = ['en' => $data['name_en'], 'ar' => $data['name_ar']];
        $translatorDescription = ['en' => $data['description_en'], 'ar' => $data['description_ar']];
        $trailData = $data['is_trail'] ? $data['trail'] : null;
        $regionId= Region::findBySlug($data['region'])->id;

        return $this->guideTripApiRepository->updateGuideTrip(
            [
                'name' => $translator,
                'description' => $translatorDescription,
                'main_price' => $data['main_price'],
                'start_datetime' => $data['start_datetime'],
                'end_datetime' => $data['end_datetime'],
                'max_attendance' => $data['max_attendance'],
                'region_id' => $regionId,
            ],
            $slug,
            isset($data['gallery']) ? $data['gallery'] : null,
            json_decode($data['activities']),
            json_decode($data['price_include']),
            isset($data['price_age']) ? json_decode($data['price_age']) : null,
            json_decode($data['assembly']),
            isset($data['required_items']) ? json_decode($data['required_items']) : null,
            json_decode($trailData),
            isset($data['main_image']) ? $data['main_image'] : null,
            json_decode($data['payment_method']),
        );
    }

    public function deleteGuideTrip($id)
    {
        return $this->guideTripApiRepository->deleteGuideTrip($id);
    }

    public function deleteImage($id)
    {
        return $this->guideTripApiRepository->deleteImage($id);
    }
    public function joinRequests($slug)
    {
        return $this->guideTripApiRepository->joinRequests($slug);
    }
    public function changeJoinRequestStatus($request)
    {
        return $this->guideTripApiRepository->changeJoinRequestStatus($request);
    }

    public function tripsOfGuide($slug)
    {
        return $this->guideTripApiRepository->tripsOfGuide($slug);
    }
}

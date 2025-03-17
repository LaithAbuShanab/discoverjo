<?php

namespace App\Repositories\Api\User;

use App\Http\Resources\AllGuideTripResource;
use App\Http\Resources\GuideResource;
use App\Http\Resources\GuideTripResource;
use App\Http\Resources\GuideTripUserResource;
use App\Http\Resources\LegalResource;
use App\Http\Resources\TopTenPlaceResource;
use App\Interfaces\Gateways\Api\User\GuideTripApiRepositoryInterface;
use App\Interfaces\Gateways\Api\User\LegalDocumentApiRepositoryInterface;
use App\Interfaces\Gateways\Api\User\TopTenPlaceApiRepositoryInterface;
use App\Models\GuideTrip;
use App\Models\GuideTripActivity;
use App\Models\GuideTripAssembly;
use App\Models\GuideTripPriceAge;
use App\Models\GuideTripPriceInclude;
use App\Models\GuideTripRequirement;
use App\Models\GuideTripTrail;
use App\Models\GuideTripUser;
use App\Models\LegalDocument;
use App\Models\TopTen;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Symfony\Component\HttpKernel\Exception\HttpException;


class EloquentGuideTripApiRepository implements GuideTripApiRepositoryInterface
{



    public function AllGuideTrip()
    {
        $perPage = config('app.pagination_per_page');
        $now = now()->setTimezone('Asia/Riyadh')->toDateTimeString();
        $guidesTrips = GuideTrip::where('status', 1)
            ->where('start_datetime', '>', $now)
            ->whereHas('guide', function ($query) {
                $query->where('status', '1'); // Ensures only trips where the guide is active
            })
            ->orderBy('start_datetime')
            ->paginate($perPage);

        GuideTrip::where('status', '1')->where('start_datetime', '<', $now)->update(['status' => '0']);

        $tripsArray = $guidesTrips->toArray();

        $pagination = [
            'next_page_url'=>$tripsArray['next_page_url'],
            'prev_page_url'=>$tripsArray['next_page_url'],
            'total' => $tripsArray['total'],
        ];

        // Pass user coordinates to the PlaceResource collection
        return [
            'trips' => AllGuideTripResource::collection($guidesTrips),
            'pagination' => $pagination
        ];


    }

    public function allGuides()
    {
        $perPage = config('app.pagination_per_page');
        $guides=User::where('status',1)->where('is_guide',1)->paginate($perPage);
        $guidesArray = $guides->toArray();

        $pagination = [
            'next_page_url'=>$guidesArray['next_page_url'],
            'prev_page_url'=>$guidesArray['next_page_url'],
            'total' => $guidesArray['total'],
        ];

        // Pass user coordinates to the PlaceResource collection
        return [
            'guides' => GuideResource::collection($guides),
            'pagination' => $pagination
        ];


    }


    public function showGuideTrip($slug)
    {
        $guideTrip = GuideTrip::findBySlug($slug);
        activityLog('Guide Trip',$guideTrip,'The user viewed guide trip','View');
        return new GuideTripResource($guideTrip);
    }

    public function storeGuideTrip($mainData, $gallery, $activities, $priceInclude, $priceAge, $assembly, $requiredItem, $trail)
    {
        DB::beginTransaction();
        try {
            // 1- Create the main table for the guide trip
            $guideTrip = GuideTrip::create($mainData);
            $guideTrip->setTranslations('name', $mainData['name']);
            $guideTrip->setTranslations('description', $mainData['description']);

            if ($gallery !== null) {
                foreach ($gallery as $image) {
                    $extension = pathinfo($image->getClientOriginalName(), PATHINFO_EXTENSION);
                    $filename = Str::random(10) . '_' . time() . '.' . $extension;
                    $guideTrip->addMedia($image)->usingFileName($filename)->toMediaCollection('guide_trip_gallery');
                }
            }

            foreach($activities as $activity){
                $activityTranslate = ['en'=>$activity->en, 'ar'=>$activity->ar];
                $guideTripActivity = new GuideTripActivity();
                $guideTripActivity->guide_trip_id = $guideTrip->id;
                $guideTripActivity->activity = $activityTranslate;
                $guideTripActivity->save();
                $guideTripActivity->activity = $guideTripActivity->setTranslations('activity', $activityTranslate);

            }

            foreach ($priceInclude as $include) {
                $priceIncludeTranslate = ['en'=>$include->en, 'ar'=>$include->ar];
                $guidePriceInclude = new GuideTripPriceInclude();
                $guidePriceInclude->guide_trip_id = $guideTrip->id;
                $guidePriceInclude->include = $priceIncludeTranslate;
                $guidePriceInclude->save();
                $guidePriceInclude->include = $guidePriceInclude->setTranslations('include', $priceIncludeTranslate);

            }

            if($priceAge)
            {
                foreach ($priceAge as $singlePrice){
                    $createPriceAge = new GuideTripPriceAge();
                    $createPriceAge->guide_trip_id = $guideTrip->id;
                    $createPriceAge->min_age = $singlePrice->min_age;
                    $createPriceAge->max_age = $singlePrice->max_age;
                    $createPriceAge->price = $singlePrice->cost;
                    $createPriceAge->save();
                }
            }

            foreach ($assembly as $singleAssembly){
                $assemblyTranslate = ['en'=>$singleAssembly->place_en, 'ar'=>$singleAssembly->place_ar];
                $assemblies = new GuideTripAssembly();
                $assemblies->guide_trip_id = $guideTrip->id;
                $assemblies->time = $singleAssembly->time;
                $assemblies->place = $assemblyTranslate;
                $assemblies->save();
                $assemblies->place = $assemblies->setTranslations('place', $assemblyTranslate);

            }

            if($requiredItem){
                foreach ($requiredItem as $singleRequiredItem){
                    $requiredItemTranslate = ['en'=>$singleRequiredItem->en, 'ar'=>$singleRequiredItem->ar];
                    $createRequiredItem = new GuideTripRequirement();
                    $createRequiredItem->guide_trip_id = $guideTrip->id;
                    $createRequiredItem->item = $requiredItemTranslate;
                    $createRequiredItem->save();
                    $createRequiredItem->item = $createRequiredItem->setTranslations('item', $requiredItemTranslate);

                }
            }

            if($trail)
            {
                $tripTrail = new GuideTripTrail();
                $tripTrail->guide_trip_id =$guideTrip->id;
                $tripTrail->min_duration_in_minute =$trail->min_duration_in_minute;
                $tripTrail->max_duration_in_minute =$trail->max_duration_in_minute;
                $tripTrail->distance_in_meter =$trail->distance_in_meter;
                $tripTrail->difficulty =$trail->difficulty;
                $tripTrail->save();
            }



            // If everything is fine, commit the transaction
            DB::commit();

            return $guideTrip;

        } catch (\Exception $e) {
            // If there is an error, rollback the transaction
            DB::rollback();

            throw new HttpException(404,$e->getMessage());

        }
    }


    public function updateGuideTrip($mainData, $slug, $gallery, $activities, $priceInclude, $priceAge, $assembly, $requiredItem, $trail)
    {
        DB::beginTransaction();
        try {
            // 1- Update the main table for the guide trip
            $guideTrip = GuideTrip::findBySlug($slug);
            $guideTrip->update($mainData);
            $guideTrip->setTranslations('name', $mainData['name']);
            $guideTrip->setTranslations('description', $mainData['description']);

            if ($gallery !== null) {
//                $guideTrip->clearMediaCollection('guide_trip_gallery');
                foreach ($gallery as $image) {
                    $extension = pathinfo($image->getClientOriginalName(), PATHINFO_EXTENSION);
                    $filename = Str::random(10) . '_' . time() . '.' . $extension;
                    $guideTrip->addMedia($image)->usingFileName($filename)->toMediaCollection('guide_trip_gallery');
                }
            }

            GuideTripActivity::where('guide_trip_id', $guideTrip->id)->delete();
            foreach($activities as $activity){
                $activityTranslate = ['en' => $activity->en, 'ar' => $activity->ar];
                $guideTripActivity = new GuideTripActivity();
                $guideTripActivity->guide_trip_id = $guideTrip->id;
                $guideTripActivity->activity = $activityTranslate;
                $guideTripActivity->save();
                $guideTripActivity->activity = $guideTripActivity->setTranslations('activity', $activityTranslate);
            }

            GuideTripPriceInclude::where('guide_trip_id', $guideTrip->id)->delete();
            foreach ($priceInclude as $include) {
                $priceIncludeTranslate = ['en' => $include->en, 'ar' => $include->ar];
                $guidePriceInclude = new GuideTripPriceInclude();
                $guidePriceInclude->guide_trip_id = $guideTrip->id;
                $guidePriceInclude->include = $priceIncludeTranslate;
                $guidePriceInclude->save();
                $guidePriceInclude->include = $guidePriceInclude->setTranslations('include', $priceIncludeTranslate);
            }

            if($priceAge) {
                GuideTripPriceAge::where('guide_trip_id', $guideTrip->id)->delete();
                foreach ($priceAge as $singlePrice) {
                    $createPriceAge = new GuideTripPriceAge();
                    $createPriceAge->guide_trip_id = $guideTrip->id;
                    $createPriceAge->min_age = $singlePrice->min_age;
                    $createPriceAge->max_age = $singlePrice->max_age;
                    $createPriceAge->price = $singlePrice->cost;
                    $createPriceAge->save();
                }
            }

            GuideTripAssembly::where('guide_trip_id', $guideTrip->id)->delete();
            foreach ($assembly as $singleAssembly) {
                $assemblyTranslate = ['en' => $singleAssembly->place_en, 'ar' => $singleAssembly->place_ar];
                $assemblies = new GuideTripAssembly();
                $assemblies->guide_trip_id = $guideTrip->id;
                $assemblies->time = $singleAssembly->time;
                $assemblies->place = $assemblyTranslate;
                $assemblies->save();
                $assemblies->place = $assemblies->setTranslations('place', $assemblyTranslate);
            }

            if($requiredItem) {
                GuideTripRequirement::where('guide_trip_id', $guideTrip->id)->delete();
                foreach ($requiredItem as $singleRequiredItem) {
                    $requiredItemTranslate = ['en' => $singleRequiredItem->en, 'ar' => $singleRequiredItem->ar];
                    $createRequiredItem = new GuideTripRequirement();
                    $createRequiredItem->guide_trip_id = $guideTrip->id;
                    $createRequiredItem->item = $requiredItemTranslate;
                    $createRequiredItem->save();
                    $createRequiredItem->item = $createRequiredItem->setTranslations('item', $requiredItemTranslate);
                }
            }

            if($trail) {
                GuideTripTrail::where('guide_trip_id', $guideTrip->id)->delete();
                $tripTrail = new GuideTripTrail();
                $tripTrail->guide_trip_id = $guideTrip->id;
                $tripTrail->min_duration_in_minute = $trail->min_duration_in_minute;
                $tripTrail->max_duration_in_minute = $trail->max_duration_in_minute;
                $tripTrail->distance_in_meter = $trail->distance_in_meter;
                $tripTrail->difficulty = $trail->difficulty;
                $tripTrail->save();
            }

            // If everything is fine, commit the transaction
            DB::commit();

            return $guideTrip;

        } catch (\Exception $e) {
            // If there is an error, rollback the transaction
            DB::rollback();

            throw new HttpException(404,$e->getMessage());

        }
    }

    public function deleteGuideTrip($slug)
    {
        $guideTrip = GuideTrip::findBySlug($slug);
        $guideTrip->clearMediaCollection('guide_trip_gallery');
        $guideTrip->delete();

    }

    public function deleteImage($id)
    {
        Media::find($id)->delete();
    }

    public function joinRequests($slug)
    {
        $guideTrip = GuideTrip::findBySlug($slug);
        $usersInGuideTrip =  $guideTrip->guideTripUsers()->whereHas('user', function ($query) {
            $query->where('status', 1);
        })->get();
        return GuideTripUserResource::collection($usersInGuideTrip);
    }


    public function changeJoinRequestStatus($request)
    {
        $status = $request['status'] == 'confirmed'?1:2;
        $guideTripUser = GuideTripUser::findOrFail($request['guide_trip_user_id']);
        $guideTripUser->update([
            'status'=>$status
        ]);
    }



}

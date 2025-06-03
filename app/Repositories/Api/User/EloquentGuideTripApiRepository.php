<?php

namespace App\Repositories\Api\User;

use App\Http\Resources\AllGuideTripResource;
use App\Http\Resources\GuideResource;
use App\Http\Resources\GuideTripResource;
use App\Http\Resources\GuideTripUpdateDetailResource;
use App\Http\Resources\GuideTripUserResource;
use App\Interfaces\Gateways\Api\User\GuideTripApiRepositoryInterface;
use App\Models\GuideTrip;
use App\Models\GuideTripActivity;
use App\Models\GuideTripAssembly;
use App\Models\GuideTripPaymentMethod;
use App\Models\GuideTripPriceAge;
use App\Models\GuideTripPriceInclude;
use App\Models\GuideTripRequirement;
use App\Models\GuideTripTrail;
use App\Models\GuideTripUser;
use App\Models\User;
use App\Notifications\Users\guide\AcceptCancelNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Str;
use LevelUp\Experience\Models\Activity;
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
            'next_page_url' => $tripsArray['next_page_url'],
            'prev_page_url' => $tripsArray['next_page_url'],
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
        $guides = User::where('status', 1)->where('is_guide', 1)->paginate($perPage);
        $guidesArray = $guides->toArray();

        $pagination = [
            'next_page_url' => $guidesArray['next_page_url'],
            'prev_page_url' => $guidesArray['next_page_url'],
            'total' => $guidesArray['total'],
        ];
        activityLog('guide', $guides->first(), 'the user view all guides ', 'view');

        // Pass user coordinates to the PlaceResource collection
        return [
            'guides' => GuideResource::collection($guides),
            'pagination' => $pagination
        ];
    }

    public function showGuideTrip($slug)
    {
        $guideTrip = GuideTrip::findBySlug($slug);
        activityLog('Guide Trip', $guideTrip, 'The user viewed spcific guide trip', 'View');
        return new GuideTripResource($guideTrip);
    }

    public function detailUpdate($slug)
    {
        $guideTrip = GuideTrip::findBySlug($slug);
        activityLog('Guide Trip', $guideTrip, 'The user show update spcific guide trip', 'View');
        return new GuideTripUpdateDetailResource($guideTrip);
    }

    public function storeGuideTrip($mainData, $gallery, $activities, $priceInclude, $priceAge, $assembly, $requiredItem, $trail,$mainImage,$paymentMethods)
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

            if (!empty($mainImage) && $mainImage->isValid()) {
                $extension = $mainImage->getClientOriginalExtension();
                $filename = Str::random(10) . '_' . time() . '.' . $extension;
                $guideTrip->addMedia($mainImage)
                    ->usingFileName($filename)
                    ->toMediaCollection('main_image');
            }

            if($paymentMethods !== null){
                foreach ($paymentMethods as $paymentMethod) {
                    $paymentMethodTranslate = ['en' => $paymentMethod->en, 'ar' => $paymentMethod->ar];
                    $guideTripPayment = new GuideTripPaymentMethod();
                    $guideTripPayment->guide_trip_id = $guideTrip->id;
                    $guideTripPayment->method = $paymentMethodTranslate;
                    $guideTripPayment->save();
                    $guideTripPayment->method = $guideTripPayment->setTranslations('method', $paymentMethodTranslate);
                }
            }

            foreach ($activities as $activity) {
                $activityTranslate = ['en' => $activity->en, 'ar' => $activity->ar];
                $guideTripActivity = new GuideTripActivity();
                $guideTripActivity->guide_trip_id = $guideTrip->id;
                $guideTripActivity->activity = $activityTranslate;
                $guideTripActivity->save();
                $guideTripActivity->activity = $guideTripActivity->setTranslations('activity', $activityTranslate);
            }

            foreach ($priceInclude as $include) {
                $priceIncludeTranslate = ['en' => $include->en, 'ar' => $include->ar];
                $guidePriceInclude = new GuideTripPriceInclude();
                $guidePriceInclude->guide_trip_id = $guideTrip->id;
                $guidePriceInclude->include = $priceIncludeTranslate;
                $guidePriceInclude->save();
                $guidePriceInclude->include = $guidePriceInclude->setTranslations('include', $priceIncludeTranslate);
            }

            if ($priceAge) {
                foreach ($priceAge as $singlePrice) {
                    $createPriceAge = new GuideTripPriceAge();
                    $createPriceAge->guide_trip_id = $guideTrip->id;
                    $createPriceAge->min_age = $singlePrice->min_age;
                    $createPriceAge->max_age = $singlePrice->max_age;
                    $createPriceAge->price = $singlePrice->cost;
                    $createPriceAge->save();
                }
            }

            foreach ($assembly as $singleAssembly) {
                $assemblyTranslate = ['en' => $singleAssembly->place_en, 'ar' => $singleAssembly->place_ar];
                $assemblies = new GuideTripAssembly();
                $assemblies->guide_trip_id = $guideTrip->id;
                $assemblies->time = $singleAssembly->time;
                $assemblies->place = $assemblyTranslate;
                $assemblies->save();
                $assemblies->place = $assemblies->setTranslations('place', $assemblyTranslate);
            }

            if ($requiredItem) {
                foreach ($requiredItem as $singleRequiredItem) {
                    $requiredItemTranslate = ['en' => $singleRequiredItem->en, 'ar' => $singleRequiredItem->ar];
                    $createRequiredItem = new GuideTripRequirement();
                    $createRequiredItem->guide_trip_id = $guideTrip->id;
                    $createRequiredItem->item = $requiredItemTranslate;
                    $createRequiredItem->save();
                    $createRequiredItem->item = $createRequiredItem->setTranslations('item', $requiredItemTranslate);
                }
            }

            if ($trail) {
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

            $user = Auth::guard('api')->user();
            $user->addPoints(10);
            $activity = Activity::find(1);
            $user->recordStreak($activity);

            return $guideTrip;
        } catch (\Exception $e) {
            // If there is an error, rollback the transaction
            DB::rollback();

            throw new HttpException(404, $e->getMessage());
        }
    }

    public function updateGuideTrip($mainData, $slug, $gallery, $activities, $priceInclude, $priceAge, $assembly, $requiredItem, $trail,$mainImage,$paymentMethods)
    {
        DB::beginTransaction();
        try {
            // 1- Update the main table for the guide trip
            $guideTrip = GuideTrip::findBySlug($slug);
            $guideTrip->update($mainData);
            $guideTrip->setTranslations('name', $mainData['name']);
            $guideTrip->setTranslations('description', $mainData['description']);

            if (!empty($mainImage) && $mainImage->isValid()) {
                // Delete the old main image if it exists
                $existingMedia = $guideTrip->getFirstMedia('main_image');
                if ($existingMedia) {
                    $existingMedia->delete();
                }

                // Add the new main image
                $extension = $mainImage->getClientOriginalExtension();
                $filename = Str::random(10) . '_' . time() . '.' . $extension;

                $guideTrip->addMedia($mainImage)
                    ->usingFileName($filename)
                    ->toMediaCollection('main_image');
            }


            if ($gallery !== null) {
                foreach ($gallery as $image) {
                    $extension = pathinfo($image->getClientOriginalName(), PATHINFO_EXTENSION);
                    $filename = Str::random(10) . '_' . time() . '.' . $extension;
                    $guideTrip->addMedia($image)->usingFileName($filename)->toMediaCollection('guide_trip_gallery');
                }
            }


            if($paymentMethods !== null){
                GuideTripPaymentMethod::where('guide_trip_id', $guideTrip->id)->delete();
                foreach ($paymentMethods as $paymentMethod) {
                    $paymentMethodTranslate = ['en' => $paymentMethod->en, 'ar' => $paymentMethod->ar];
                    $guideTripPayment = new GuideTripPaymentMethod();
                    $guideTripPayment->guide_trip_id = $guideTrip->id;
                    $guideTripPayment->method = $paymentMethodTranslate;
                    $guideTripPayment->save();
                    $guideTripPayment->method = $guideTripPayment->setTranslations('method', $paymentMethodTranslate);
                }
            }

            GuideTripActivity::where('guide_trip_id', $guideTrip->id)->delete();
            foreach ($activities as $activity) {
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

            if ($priceAge) {
                GuideTripPriceAge::where('guide_trip_id', $guideTrip->id)->delete();
                foreach ($priceAge as $singlePrice) {
                    $createPriceAge = new GuideTripPriceAge();
                    $createPriceAge->guide_trip_id = $guideTrip->id;
                    $createPriceAge->min_age = $singlePrice->min_age;
                    $createPriceAge->max_age = $singlePrice->max_age;
                    $createPriceAge->price = $singlePrice->cost;
                    $createPriceAge->save();
                }
            }else{
                GuideTripPriceAge::where('guide_trip_id', $guideTrip->id)->delete();
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

            if ($requiredItem) {
                GuideTripRequirement::where('guide_trip_id', $guideTrip->id)->delete();
                foreach ($requiredItem as $singleRequiredItem) {
                    $requiredItemTranslate = ['en' => $singleRequiredItem->en, 'ar' => $singleRequiredItem->ar];
                    $createRequiredItem = new GuideTripRequirement();
                    $createRequiredItem->guide_trip_id = $guideTrip->id;
                    $createRequiredItem->item = $requiredItemTranslate;
                    $createRequiredItem->save();
                    $createRequiredItem->item = $createRequiredItem->setTranslations('item', $requiredItemTranslate);
                }
            }else{
                GuideTripRequirement::where('guide_trip_id', $guideTrip->id)->delete();
            }

            if ($trail) {
                GuideTripTrail::where('guide_trip_id', $guideTrip->id)->delete();
                $tripTrail = new GuideTripTrail();
                $tripTrail->guide_trip_id = $guideTrip->id;
                $tripTrail->min_duration_in_minute = $trail->min_duration_in_minute;
                $tripTrail->max_duration_in_minute = $trail->max_duration_in_minute;
                $tripTrail->distance_in_meter = $trail->distance_in_meter;
                $tripTrail->difficulty = $trail->difficulty;
                $tripTrail->save();
            }else{
                GuideTripTrail::where('guide_trip_id', $guideTrip->id)->delete();
            }

            // If everything is fine, commit the transaction
            DB::commit();

            return $guideTrip;
        } catch (\Exception $e) {
            // If there is an error, rollback the transaction
            DB::rollback();

            throw new HttpException(404, $e->getMessage());
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
        $media = Media::find($id);
        if ($media->collection_name === 'main_image') {
            return response()->json(['status' => 400, 'msg' => 'You cannot delete the main image update it in Guide trip update screen'], 400);
        }
        $media->delete();
    }

    public function joinRequests($slug)
    {
        $guideTrip = GuideTrip::findBySlug($slug);
        $usersInGuideTrip =  $guideTrip->guideTripUsers()->whereHas('user', function ($query) {
            $query->where('status', 1);
        })->get();
        activityLog('Guide trip', $usersInGuideTrip->first(), 'the guide view join requests for trip', 'view');

        return GuideTripUserResource::collection($usersInGuideTrip);
    }

    public function changeJoinRequestStatus($request)
    {
        return DB::transaction(function () use ($request) {

            $status = $request['status'] === 'confirmed' ? 1 : 2;
            $guideTripUser = GuideTripUser::findOrFail($request['guide_trip_user_id']);
            $guideTripUser->update([
                'status' => $status
            ]);

            $userMember = $guideTripUser->user;
            $receiverLanguage = $userMember->lang ?? 'en';
            $tokens = $userMember->DeviceTokenMany->pluck('token')->toArray();

            $trip = $guideTripUser->guideTrip;

            // Save notification in DB (to the user)
            Notification::send($userMember, new AcceptCancelNotification($trip, $status, $guideTripUser));

            $fullName = $guideTripUser->first_name . ' ' . $guideTripUser->last_name;

            // Send push notification
            if ($status == 1) {
                $notification = [
                    'title' => Lang::get('app.notifications.accepted-guide-trip-title', ['username' => $fullName], $receiverLanguage),
                    'body'  => Lang::get('app.notifications.accepted-guide-trip-body', [
                        'username'  => $fullName,
                        'trip_name' => $trip->name
                    ], $receiverLanguage),
                    'icon'  => asset('assets/icon/trip.png'),
                    'sound' => 'default',
                ];
            } else {
                $notification = [
                    'title' => Lang::get('app.notifications.declined-guide-trip-title', ['username' => $fullName], $receiverLanguage),
                    'body'  => Lang::get('app.notifications.declined-guide-trip-body', [
                        'username'  => $fullName,
                        'trip_name' => $trip->name
                    ], $receiverLanguage),
                    'icon'  => asset('assets/icon/trip.png'),
                    'sound' => 'default',
                ];
            }

            if (!empty($tokens)) {
                sendNotification($tokens, $notification);
            }
            $user = Auth::guard('api')->user();
            $user->addPoints(10);
            $activity = Activity::find(1);
            $user->recordStreak($activity);
        });
    }

    public function tripsOfGuide($slug)
    {
        $perPage = config('app.pagination_per_page');
        $guide = User::findBySlug($slug);
        $guideTrips = GuideTrip::where('guide_id', $guide->id)->orderBy('start_datetime', 'desc')
            ->paginate($perPage);
        $tripsArray = $guideTrips->toArray();

        $pagination = [
            'next_page_url' => $tripsArray['next_page_url'],
            'prev_page_url' => $tripsArray['next_page_url'],
            'total' => $tripsArray['total'],
        ];


        // Pass user coordinates to the PlaceResource collection
        return [
            'trips' => AllGuideTripResource::collection($guideTrips),
            'pagination' => $pagination
        ];
    }
}

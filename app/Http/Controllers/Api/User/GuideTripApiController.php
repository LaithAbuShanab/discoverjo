<?php

namespace App\Http\Controllers\Api\User;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\User\GuideTrip\CreateGuideTripRequest;
use App\Http\Requests\Api\User\GuideTrip\UpdateGuideTripRequest;
use App\Rules\CheckAgeGenderExistenceRule;
use App\Rules\CheckIfExistsInFavoratblesRule;
use App\Rules\CheckIfGuideIsOwnerOfTrip;
use App\Rules\CheckIfImageBelongToGuideRule;
use App\Rules\CheckIfNotExistsInFavoratblesRule;
use App\Rules\CheckIfTheUserOwnTheTripRule;
use App\UseCases\Api\User\GuideTripApiUseCase;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class GuideTripApiController extends Controller
{
    protected $guideTripApiUseCase;

    public function __construct(GuideTripApiUseCase $guideTripApiUseCase)
    {
        $this->guideTripApiUseCase = $guideTripApiUseCase;
    }

    public function index()
    {
        try {
            $guideTrips = $this->guideTripApiUseCase->AllGuideTrip();
            return ApiResponse::sendResponse(200, __('app.api.guide-trips-retrieved-successfully'), $guideTrips);
        } catch (\Exception $e) {
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $e->getMessage());
        }
    }

    public function allGuides()
    {
        try {
            $guideTrips = $this->guideTripApiUseCase->allGuides();
            return ApiResponse::sendResponse(200, __('app.api.guide-trips-retrieved-successfully'), $guideTrips);
        } catch (\Exception $e) {
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $e->getMessage());
        }
    }

    public function show(Request $request)
    {
        $id = $request->guide_trip_slug;

        $validator = Validator::make(['guide_trip_slug' => $id], [
            'guide_trip_slug' => ['required', 'exists:guide_trips,slug'],
        ],[
            'guide_trip_slug.required'=>__('validation.api.guide-trip-id-required'),
            'guide_trip_slug.exists'=>__('validation.api.guide-trip-id-does-not-exists'),
        ]);
        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $errors);
        }
        try {
            $data = $validator->validated();
            $updateTrip = $this->guideTripApiUseCase->showGuideTrip($data['guide_trip_slug']);
            return ApiResponse::sendResponse(200, __('app.api.trip-retrieved-successfully'), $updateTrip);
        } catch (\Exception $e) {
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST, $e->getMessage());
        }
    }

    public function store(CreateGuideTripRequest $request)
    {
        try {
            $createTrip = $this->guideTripApiUseCase->storeGuideTrip($request->validated());
            return ApiResponse::sendResponse(200, __('app.api.trip-created-successfully'), $createTrip);
        } catch (\Exception $e) {
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $e->getMessage());
        }
    }

    public function update(UpdateGuideTripRequest $request)
    {
        $id = $request->guide_trip_id;

        $validator = Validator::make(['guide_trip_id' => $id], [
            'guide_trip_id' => ['required', 'exists:guide_trips,id' ,new CheckIfGuideIsOwnerOfTrip()],
        ],[
            'guide_trip_id.required'=>__('validation.api.guide-trip-id-required'),
            'guide_trip_id.exists'=>__('validation.api.guide-trip-id-does-not-exists'),
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $errors);
        }
        try {
            $updateTrip = $this->guideTripApiUseCase->updateGuideTrip($request->validated(), $id);
            return ApiResponse::sendResponse(200, __('app.api.trip-updated-successfully'), $updateTrip);
        } catch (\Exception $e) {
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST, $e->getMessage());
        }
    }

    public function delete(Request $request)
    {
        $id = $request->guide_trip_id;

        $validator = Validator::make(['guide_trip_id' => $id], [
            'guide_trip_id' => ['required', 'exists:guide_trips,id' ,new CheckIfGuideIsOwnerOfTrip()],
        ],[
            'guide_trip_id.required'=>__('validation.api.guide-trip-id-required'),
            'guide_trip_id.exists'=>__('validation.api.guide-trip-id-does-not-exists'),
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $errors);
        }
        try {
            $updateTrip = $this->guideTripApiUseCase->deleteGuideTrip( $id);
            return ApiResponse::sendResponse(200, __('app.api.trip-deleted-successfully'), $updateTrip);
        } catch (\Exception $e) {
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST, $e->getMessage());
        }
    }

    public function DeleteImage(Request $request)
    {
        $id = $request->media_id;
        $validator = Validator::make(['media_id' => $id], [
            'media_id' => ['required', 'exists:media,id',new CheckIfImageBelongToGuideRule()],
        ],[
            'media_id.required'=>__('validation.api.media-id-required'),
            'media_id.exists'=>__('validation.api.media-id-does-not-exists'),
        ]);

        if ($validator->fails()) {
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $validator->errors()->messages()['media_id'][0]);
        }

        try {
            $createTrip = $this->guideTripApiUseCase->deleteImage($id);
            return ApiResponse::sendResponse(200, __('app.api.trip-image-deleted-successfully'), $createTrip);
        } catch (\Exception $e) {
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $e->getMessage());
        }
    }

    public function joinRequests(Request $request)
    {
        $id = $request->guide_trip_id;

        $validator = Validator::make(['guide_trip_id' => $id], [
            'guide_trip_id' => ['required', 'exists:guide_trips,id' ,new CheckIfGuideIsOwnerOfTrip()],
        ],[
            'guide_trip_id.required'=>__('validation.api.guide-trip-id-required'),
            'guide_trip_id.exists'=>__('validation.api.guide-trip-id-does-not-exists'),
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $errors);
        }
        try {
            $updateTrip = $this->guideTripApiUseCase->joinRequests($id);
            return ApiResponse::sendResponse(200, __('app.api.join-requests-retrieved-successfully'), $updateTrip);
        } catch (\Exception $e) {
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST, $e->getMessage());
        }
    }

    public function changeJoinRequestStatus(Request $request)
    {

        $validator = Validator::make([
            'guide_trip_user_id' => $request->guide_trip_user_id,
            'status' => $request->status,
            ],
            [
            'guide_trip_user_id' => ['required', 'exists:guide_trip_users,id' ,new CheckIfTheUserOwnTheTripRule()],
             'status' => ['required', Rule::in(['confirmed', 'canceled'])],
        ],[
            'status.required'=>__('validation.api.status-is-required'),
            'guide_trip_user_id.required'=>__('validation.api.guide-trip-user-id-required'),
            'guide_trip_user_id.exists'=>__('validation.api.guide-trip-user-id-does-not-exists'),
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $errors);
        }
        try {
            $updateTrip = $this->guideTripApiUseCase->changeJoinRequestStatus($request);
            return ApiResponse::sendResponse(200, __('app.api.join-requests-status-changed-successfully'), $updateTrip);
        } catch (\Exception $e) {
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST, $e->getMessage());
        }
    }


}

<?php

namespace App\Http\Controllers\Api\User;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\User\GuideTrip\CreateGuideTripUserRequest;
use App\Http\Requests\Api\User\GuideTrip\UpdateGuideTripUserRequest;
use App\Rules\CheckIfGuideActiveRule;
use App\Rules\CheckIfGuideTripActiveOrInFuture;
use App\Rules\CheckIfGuideTripUserExistRule;
use App\Rules\CheckIfUserHasJoinedInTripRule;
use App\Rules\CheckIfUserHasJoinedRule;
use App\UseCases\Api\User\GuideTripUserApiUseCase;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class GuideTripUserApiController extends Controller
{
    public function __construct(protected GuideTripUserApiUseCase $guideTripUserApiUseCase)
    {
        $this->guideTripUserApiUseCase = $guideTripUserApiUseCase;
    }

    public function index()
    {
        try {
            $guideTrips = $this->guideTripUserApiUseCase->AllUsersForGuideTrip();
            return ApiResponse::sendResponse(200, __('app.api.guide-trips-retrieved-successfully'), $guideTrips);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $e->getMessage());
        }
    }

    public function allSubscription(Request $request, $guide_trip_slug)
    {

        $validator = Validator::make(['guide_trip_slug' => $guide_trip_slug], [
            'guide_trip_slug' => ['required', 'exists:guide_trips,slug', new CheckIfUserHasJoinedRule(), new CheckIfGuideActiveRule()],
        ], [
            'guide_trip_slug.required' => __('validation.api.guide-trip-id-required'),
            'guide_trip_slug.exists' => __('validation.api.guide-trip-id-does-not-exists'),
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $errors);
        }

        try {
            $guideTrips = $this->guideTripUserApiUseCase->allSubscription($validator->validated()['guide_trip_slug']);
            return ApiResponse::sendResponse(200, __('app.api.guide-trips-users-subscription-fetched-successfully'), $guideTrips);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $e->getMessage());
        }
    }

    public function store(CreateGuideTripUserRequest $request, $guide_trip_slug)
    {
        $validator = Validator::make(['guide_trip_slug' => $guide_trip_slug], [
            'guide_trip_slug' => ['bail', 'required', 'exists:guide_trips,slug', new CheckIfGuideTripActiveOrInFuture(), new CheckIfGuideTripUserExistRule(), new CheckIfGuideActiveRule()],
        ], [
            'guide_trip_slug.required' => __('validation.api.guide-trip-id-required'),
            'guide_trip_slug.exists' => __('validation.api.guide-trip-id-does-not-exists'),
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $errors);
        }
        $data =  array_merge($request->validated(), $validator->validated());;
        try {
            $sendRequest = $this->guideTripUserApiUseCase->storeSubscriberInTrip($data);
            return ApiResponse::sendResponse(200, __('app.api.guide-trips-subscription-created-successfully'), $sendRequest);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $e->getMessage());
        }
    }

    public function update(UpdateGuideTripUserRequest $request, $guide_trip_slug)
    {
        $validator = Validator::make(['guide_trip_slug' => $guide_trip_slug], [
            'guide_trip_slug' => ['bail', 'required', 'exists:guide_trips,slug', new CheckIfGuideTripActiveOrInFuture(), new CheckIfUserHasJoinedInTripRule(), new CheckIfGuideActiveRule()],
        ], [
            'guide_trip_slug.required' => __('validation.api.guide-trip-id-required'),
            'guide_trip_slug.exists' => __('validation.api.guide-trip-id-does-not-exists'),
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $errors);
        }
        $data =  array_merge($request->validated(), $validator->validated());
        try {
            $updateRequest = $this->guideTripUserApiUseCase->updateSubscriberInTrip($data);
            return ApiResponse::sendResponse(200, __('app.api.guide-trips-users-subscription-updated-successfully'), $updateRequest);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $e->getMessage());
        }
    }

    public function delete(Request $request, $guide_trip_slug)
    {
        $validator = Validator::make(['guide_trip_slug' => $guide_trip_slug], [
            'guide_trip_slug' => ['bail', 'required', 'exists:guide_trips,slug', new CheckIfUserHasJoinedRule(), new CheckIfGuideActiveRule()],
        ], [
            'guide_trip_slug.required' => __('validation.api.guide-trip-id-required'),
            'guide_trip_slug.exists' => __('validation.api.guide-trip-id-does-not-exists'),
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $errors);
        }
        try {
            $deleteRequest = $this->guideTripUserApiUseCase->deleteSubscriberInTrip($validator->validated()['guide_trip_slug']);
            return ApiResponse::sendResponse(200, __('app.api.guide-trips-users-subscription-deleted-successfully'), $deleteRequest);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $e->getMessage());
        }
    }

    public function search(Request $request)
    {
        $query = $request->input('query');
        try {
            $places = $this->guideTripUserApiUseCase->search($query);
            return ApiResponse::sendResponse(200, __('app.api.the-searched-guide-trip-retrieved-successfully'), $places);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $e->getMessage());
        }
    }
}

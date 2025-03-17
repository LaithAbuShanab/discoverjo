<?php

namespace App\Http\Controllers\Api\User;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\User\GuideTrip\CreateGuideTripUserRequest;
use App\Http\Requests\Api\User\GuideTrip\UpdateGuideTripRequest;
use App\Http\Requests\Api\User\GuideTrip\UpdateGuideTripUserRequest;
use App\Rules\CheckIfExistsInFavoratblesRule;
use App\Rules\CheckIfExistsInReviewsRule;
use App\Rules\CheckIfExistsInToUpdateReviewsRule;
use App\Rules\CheckIfGuideTripActiveOrInFuture;
use App\Rules\CheckIfGuideTripUserExistRule;
use App\Rules\CheckIfNotExistsInFavoratblesRule;
use App\Rules\CheckIfPastEventOrVolunteering;
use App\Rules\CheckIfUserHasJoinedInTripRule;
use App\Rules\CheckIfUserHasJoinedRule;
use App\Rules\CheckUserTripExistsRule;
use App\UseCases\Api\User\GuideTripUserApiUseCase;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class GuideTripUserApiController extends Controller
{
    protected $guideTripUserApiUseCase;

    public function __construct(GuideTripUserApiUseCase $guideTripUserApiUseCase)
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

    public function allSubscription(Request $request,$guide_trip_slug)
    {

        $validator = Validator::make(['guide_trip_slug' => $guide_trip_slug], [
            'guide_trip_slug' => ['required', 'exists:guide_trips,slug',new CheckIfUserHasJoinedRule()],
        ],[
            'guide_trip_slug.required'=>__('validation.api.guide-trip-id-required'),
            'guide_trip_slug.exists'=>__('validation.api.guide-trip-id-does-not-exists'),
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $errors);
        }
        try {
            $createGuideTripUsers = $this->guideTripUserApiUseCase->allSubscription($validator->validated()['guide_trip_slug']);
            return ApiResponse::sendResponse(200, __('app.api.guide-trips-users-subscription-fetched-successfully'), $createGuideTripUsers);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $e->getMessage());
        }

    }

    public function store(CreateGuideTripUserRequest $request,$guide_trip_slug)
    {
        $validator = Validator::make(['guide_trip_slug' => $guide_trip_slug], [
            'guide_trip_slug' => ['required','exists:guide_trips,slug',new CheckIfGuideTripActiveOrInFuture(),new CheckIfGuideTripUserExistRule()],
        ],[
            'guide_trip_slug.required'=>__('validation.api.guide-trip-id-required'),
            'guide_trip_slug.exists'=>__('validation.api.guide-trip-id-does-not-exists'),
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $errors);
        }
        $data =  array_merge($request->validated(), $validator->validated());;
        try {
            $createGuideTripUsers = $this->guideTripUserApiUseCase->storeSubscriberInTrip($data);
            return ApiResponse::sendResponse(200, __('app.api.guide-trips-subscription-created-successfully'), $createGuideTripUsers);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $e->getMessage());
        }
    }

    public function update(UpdateGuideTripUserRequest $request,$guide_trip_slug)
    {
        $validator = Validator::make(['guide_trip_slug' => $guide_trip_slug], [
            'guide_trip_slug' => ['bail','required','exists:guide_trips,slug',new CheckIfGuideTripActiveOrInFuture() ,new CheckIfUserHasJoinedInTripRule()],
        ],[
            'guide_trip_slug.required'=>__('validation.api.guide-trip-id-required'),
            'guide_trip_slug.exists'=>__('validation.api.guide-trip-id-does-not-exists'),
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $errors);
        }
        $data =  array_merge($request->validated(), $validator->validated());;
        try {
            $createGuideTripUsers = $this->guideTripUserApiUseCase->updateSubscriberInTrip($data);
            return ApiResponse::sendResponse(200, __('app.api.guide-trips-users-subscription-updated-successfully'), $createGuideTripUsers);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $e->getMessage());
        }

    }

    public function delete(Request $request,$guide_trip_slug)
    {

        $validator = Validator::make(['guide_trip_slug' => $guide_trip_slug], [
            'guide_trip_slug' => ['bail','required', 'exists:guide_trips,slug',new CheckIfUserHasJoinedRule()],
        ],[
            'guide_trip_slug.required'=>__('validation.api.guide-trip-id-required'),
            'guide_trip_slug.exists'=>__('validation.api.guide-trip-id-does-not-exists'),
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $errors);
        }
        try {
            $createGuideTripUsers = $this->guideTripUserApiUseCase->deleteSubscriberInTrip($validator->validated()['guide_trip_slug']);
            return ApiResponse::sendResponse(200, __('app.api.guide-trips-users-subscription-deleted-successfully'), $createGuideTripUsers);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $e->getMessage());
        }

    }

    public function createFavoriteGuideTrip(Request $request)
    {
        $id = $request->guide_trip_id;
        $validator = Validator::make(['guide_trip_id' => $id], [
            'guide_trip_id' => ['required', 'exists:guide_trips,id', new CheckIfExistsInFavoratblesRule('App\Models\GuideTrip')],
        ],[
            'guide_trip_id.required'=>__('validation.api.guide-trip-id-required'),
            'guide_trip_id.exists'=>__('validation.api.guide-trip-id-does-not-exists'),
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $errors);
        }
        try {
            $trip = $this->guideTripUserApiUseCase->favorite($id);
            return ApiResponse::sendResponse(200, __('app.api.you-added-trip-in-favorite-successfully'), $trip);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST, $e->getMessage());
        }
    }

    public function deleteFavoriteGuideTrip(Request $request)
    {
        $id = $request->guide_trip_id;
        $validator = Validator::make(['guide_trip_id' => $id], [
            'guide_trip_id' => ['required', 'exists:guide_trips,id', new CheckIfNotExistsInFavoratblesRule('App\Models\GuideTrip')],
        ],[
            'guide_trip_id.required'=>__('validation.api.guide-trip-id-required'),
            'guide_trip_id.exists'=>__('validation.api.guide-trip-id-does-not-exists'),
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $errors);
        }
        try {
            $trip = $this->guideTripUserApiUseCase->deleteFavorite($id);
            return ApiResponse::sendResponse(200, __('app.api.you-deleted-the-trip-from-favorite-Successfully'), $trip);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST, $e->getMessage());
        }
    }

    public function addReview(Request $request)
    {
        $validator = Validator::make([
            'guide_trip_id' => $request->guide_trip_id,
            'rating' => $request->rating,
            'comment' => $request->comment
        ], [
            'guide_trip_id' => ['required', 'exists:guide_trips,id', new CheckIfExistsInReviewsRule('App\Models\GuideTrip'), new CheckIfPastEventOrVolunteering('App\Models\GuideTrip')],
            'rating' => ['required', 'numeric'],
            'comment' => ['nullable', 'string']
        ],[
            'guide_trip_id.required'=>__('validation.api.guide-trip-id-required'),
            'guide_trip_id.exists'=>__('validation.api.guide-trip-id-does-not-exists'),
            'rating.required' => __('validation.api.rating-is-required'),
            'comment.string'=>__('validation.api.comment-should-be-string'),
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $errors);
        }

        try {
            $trip = $this->guideTripUserApiUseCase->addReview($validator->validated());
            return ApiResponse::sendResponse(200, __('app.api.you-added-review-for-this-trip-successfully'), $trip);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST, $e->getMessage());
        }
    }

    public function updateReview(Request $request)
    {

        $validator = Validator::make([
            'guide_trip_id' => $request->guide_trip_id,
            'rating' => $request->rating,
            'comment' => $request->comment
        ], [
            'guide_trip_id' => ['required', 'exists:guide_trips,id', new CheckIfExistsInToUpdateReviewsRule('App\Models\GuideTrip')],
            'rating' => ['required', 'numeric'],
            'comment' => ['nullable', 'string']
        ],[
            'guide_trip_id.required'=>__('validation.api.guide-trip-id-required'),
            'guide_trip_id.exists'=>__('validation.api.guide-trip-id-does-not-exists'),
            'rating.required' => __('validation.api.rating-is-required'),
            'comment.string'=>__('validation.api.comment-should-be-string'),
        ]);


        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $errors);
        }
        try {
            $event = $this->guideTripUserApiUseCase->updateReview($validator->validated());
            return ApiResponse::sendResponse(200, __('app.api.your-review-in-this-trip-updated-successfully'), $event);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST, $e->getMessage());
        }
    }

    public function deleteReview(Request $request)
    {
        $validator = Validator::make([
            'guide_trip_id' => $request->guide_trip_id,
        ], [
            'guide_trip_id' => ['required', 'exists:guide_trips,id', new CheckIfExistsInToUpdateReviewsRule('App\Models\GuideTrip')],
        ],[
            'guide_trip_id.required'=>__('validation.api.guide-trip-id-required'),
            'guide_trip_id.exists'=>__('validation.api.guide-trip-id-does-not-exists'),
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $errors);
        }
        try {
            $event = $this->guideTripUserApiUseCase->deleteReview($validator->validated());
            return ApiResponse::sendResponse(200, __('app.api.you-deleted-your-review-successfully'), $event);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST, $e->getMessage());
        }
    }

    public function likeDislike(Request $request)
    {
        $validator = Validator::make(
            [
                'status' => $request->status,
                'review_id' => $request->review_id,
            ],
            [
                'status' => ['required', Rule::in(['like', 'dislike'])],
                'review_id' => ['required', 'integer', 'exists:reviewables,id'],
            ],
            [
                'review_id.exists' => __('validation.api.the-selected-review-id-does-not-exists'),
                'review_id.required'=> __('validation.api.the-review-id-required'),
                'status'=>__('validation.api.the-status-required')
            ]);

        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $errors);
        }

        try {
            $this->guideTripUserApiUseCase->reviewsLike($request);
            return ApiResponse::sendResponse(200,__('app.event.api.the-likable-status-change-successfully'), []);
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

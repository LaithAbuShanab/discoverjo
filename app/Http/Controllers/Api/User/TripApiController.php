<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Rules\CheckIfExistsInToUpdateReviewsRule;
use App\Rules\CheckIfTheOwnerOfTripActiveRule;
use App\Rules\CheckUserTripExistsRule;
use App\UseCases\Api\User\TripApiUseCase;
use Illuminate\Http\Response;
use App\Helpers\ApiResponse;
use App\Http\Requests\Api\User\Trip\AcceptCancelInvitationsRequest;
use App\Http\Requests\Api\User\Trip\AcceptCancelUserRequest;
use App\Http\Requests\Api\User\Trip\CreateTripRequest;
use App\Http\Requests\Api\User\Trip\UpdateTripRequest;
use App\Rules\CheckAgeGenderExistenceRule;
use App\Rules\CheckIfCanUpdateTripRule;
use App\Rules\CheckOwnerTripRule;
use App\Rules\CheckRemoveUserTripRule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class TripApiController extends Controller
{
    protected $tripApiUseCase;

    public function __construct(TripApiUseCase $tripApiUseCase)
    {
        $this->tripApiUseCase = $tripApiUseCase;
    }

    public function index()
    {
        try {
            $trips = $this->tripApiUseCase->trips();
            return ApiResponse::sendResponse(200, __('app.api.retrieved-successfully'), $trips);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $e->getMessage());
        }
    }

    public function allTrip()
    {
        try {
            $trips = $this->tripApiUseCase->allTrips();
            return ApiResponse::sendResponse(200, __('app.trips-retrieved-successfully'), $trips);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);

            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $e->getMessage());
        }
    }

    public function invitationTrips()
    {
        try {
            $trips = $this->tripApiUseCase->invitationTrips();
            return ApiResponse::sendResponse(200, __('app.api.retrieved-successfully'), $trips);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);

            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $e->getMessage());
        }
    }

    public function invitationCount(){
        try {
            $count = $this->tripApiUseCase->invitationCount();
            return ApiResponse::sendResponse(200, __('app.api.retrieved-successfully'), $count);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $e->getMessage());
        }
    }

    public function acceptCancelInvitation(AcceptCancelInvitationsRequest $request)
    {
        $validator = Validator::make(
            ['status' => $request->status],
            ['status' => ['required', Rule::in(['accept', 'cancel'])],]
        );

        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $errors);
        }

        try {
            $this->tripApiUseCase->changeStatusInvitation($request);
            return ApiResponse::sendResponse(200, __('app.api.the-status-change-successfully'), []);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);

            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $e->getMessage());
        }
    }

    public function tags()
    {
        try {
            $tags = $this->tripApiUseCase->tags();
            return ApiResponse::sendResponse(200, __('app.api.retrieved-successfully'), $tags);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);

            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $e->getMessage());
        }
    }

    public function create(CreateTripRequest $request)
    {
        try {
            $createTrip = $this->tripApiUseCase->createTrip($request);
            return ApiResponse::sendResponse(200, __('app.api.trip-created-successfully'), $createTrip);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);

            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $e->getMessage());
        }
    }

    public function join(Request $request)
    {
        $slug = $request->trip_slug;

        $validator = Validator::make(['trip_slug' => $slug], [
            'trip_slug' => ['bail', 'required', 'exists:trips,slug', new CheckAgeGenderExistenceRule(), new CheckIfTheOwnerOfTripActiveRule()],
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $errors);
        }

        try {
            $this->tripApiUseCase->joinTrip($slug);
            return ApiResponse::sendResponse(200, __('app.api.you-join-to-trip-successfully'), []);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);

            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $e->getMessage());
        }
    }

    public function cancelJoin(Request $request)
    {
        $slug = $request->trip_slug;

        $validator = Validator::make(['trip_slug' => $slug], [
            'trip_slug' => ['bail', 'required', 'exists:trips,slug', new CheckUserTripExistsRule()],
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $errors);
        }

        try {
            $this->tripApiUseCase->cancelJoinTrip($slug, $request);
            return ApiResponse::sendResponse(200, __('app.api.you-are-left-from-the-trip-successfully'), []);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);

            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $e->getMessage());
        }
    }

    public function privateTrips()
    {
        try {
            $trips = $this->tripApiUseCase->privateTrips();
            return ApiResponse::sendResponse(200, __('app.api.retrieved-successfully'), $trips);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $e->getMessage());
        }
    }

    public function tripDetails(Request $request)
    {
        $slug = $request->trip_slug;
        $validator = Validator::make(['trip_slug' => $slug], [
            'trip_slug' => ['required', 'exists:trips,slug',new CheckIfTheOwnerOfTripActiveRule()],
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $errors);
        }
        try {
            $details = $this->tripApiUseCase->tripDetails($request->trip_slug);
            return ApiResponse::sendResponse(200, __('app.api.retrieved-successfully'), $details);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);

            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $e->getMessage());
        }
    }

    public function acceptCancel(AcceptCancelUserRequest $request)
    {
        $validator = Validator::make(
            ['status' => $request->status],
            ['status' => ['required', Rule::in(['accept', 'cancel'])],]
        );

        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $errors);
        }

        try {
            $this->tripApiUseCase->changeStatus($request);
            return ApiResponse::sendResponse(200, __('app.api.the-status-change-successfully'), []);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);

            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $e->getMessage());
        }
    }

    public function updateReview(Request $request)
    {

        $validator = Validator::make([
            'trip_id' => $request->trip_id,
            'rating' => $request->rating,
            'comment' => $request->comment
        ], [
            'trip_id' => ['required', 'exists:trips,id', new CheckIfExistsInToUpdateReviewsRule('App\Models\Trip')],
            'rating' => ['required', 'numeric'],
            'comment' => ['nullable', 'string']
        ]);


        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $errors);
        }
        try {
            $trip = $this->tripApiUseCase->updateReview($validator->validated());
            return ApiResponse::sendResponse(200, __('app.you-update-review-in-trip-successfully'), $trip);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);

            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST, $e->getMessage());
        }
    }

    public function deleteReview(Request $request)
    {
        $validator = Validator::make([
            'trip_id' => $request->trip_id,
        ], [
            'trip_id' => ['required', 'exists:trips,id', new CheckIfExistsInToUpdateReviewsRule('App\Models\Trip')],
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $errors);
        }
        try {
            $trip = $this->tripApiUseCase->deleteReview($validator->validated());
            return ApiResponse::sendResponse(200, __('app.you-delete-review-for-trip-successfully'), $trip);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);

            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST, $e->getMessage());
        }
    }

    public function reviews(Request $request)
    {
        $validator = Validator::make([
            'trip_id' => $request->trip_id,
        ], [
            'trip_id' => ['required', 'exists:trips,id'],
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $errors);
        }
        try {
            $trip = $this->tripApiUseCase->allReviews($validator->validated());
            return ApiResponse::sendResponse(200, __('app.trip-reviews-retrieved-successfully'), $trip);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);

            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST, $e->getMessage());
        }
    }

    public function likeDislike(Request $request)
    {
        $validator = Validator::make(
            ['status' => $request->status, 'review_id' => $request->review_id,],
            ['status' => ['required', Rule::in(['like', 'dislike'])], 'review_id' => ['required', 'integer', 'exists:reviewables,id'],]
        );

        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $errors);
        }

        try {
            $this->tripApiUseCase->reviewsLike($request);
            return ApiResponse::sendResponse(200, __('app.the-status-change-successfully'), []);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);

            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $e->getMessage());
        }
    }

    public function remove(Request $request)
    {
        $validator = Validator::make(['trip_slug' => $request->trip_slug], [
            'trip_slug' => ['bail', 'required', 'exists:trips,slug', new CheckOwnerTripRule],
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $errors);
        }

        try {
            $this->tripApiUseCase->remove($request->trip_slug);
            return ApiResponse::sendResponse(200, __('app.api.the-trip-deleted-successfully'), []);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);

            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $e->getMessage());
        }
    }

    public function update(UpdateTripRequest $request)
    {
        $validator = Validator::make(['trip_slug' => $request->trip_slug], [
            'trip_slug' => ['required', 'exists:trips,slug', new CheckIfCanUpdateTripRule],
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $errors);
        }

        try {
            $this->tripApiUseCase->update($request);
            return ApiResponse::sendResponse(200, __('app.api.the-trip-updated-successfully'), []);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);

            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $e->getMessage());
        }
    }

    public function search(Request $request)
    {
        $query = $request->input('query');
        try {
            $places = $this->tripApiUseCase->search($query);
            return ApiResponse::sendResponse(200, __('app.the-searched-trip-retrieved-successfully'), $places);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);

            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $e->getMessage());
        }
    }

    public function removeUser(Request $request)
    {
        $validator = Validator::make(
            ['trip_slug' => $request->trip_slug, 'user_slug' => $request->user_slug],
            [
                'trip_slug' => ['required', 'exists:trips,slug', new CheckRemoveUserTripRule()],
                'user_slug' => ['required', 'exists:users,slug'],
            ]
        );
        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $errors);
        }
        try {
            $this->tripApiUseCase->removeUser($request);
            return ApiResponse::sendResponse(200, __('app.api.the-user-deleted-successfully'), []);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $e->getMessage());
        }
    }
}

<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Rules\CheckIfExistsInFavoratblesRule;
use App\Rules\CheckIfExistsInReviewsRule;
use App\Rules\CheckIfExistsInToUpdateReviewsRule;
use App\Rules\CheckIfNotExistsInFavoratblesRule;
use App\Rules\CheckIfUserAttendanceAndPastTripRule;
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
use Illuminate\Http\Request;
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
            return ApiResponse::sendResponse(200, __('app.trips-retrieved-successfully'), $trips);
        } catch (\Exception $e) {
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $e->getMessage());
        }
    }

    public function allTrip()
    {
        try {
            $trips = $this->tripApiUseCase->allTrips();
            return ApiResponse::sendResponse(200, __('app.trips-retrieved-successfully'), $trips);
        } catch (\Exception $e) {
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $e->getMessage());
        }
    }

    public function invitationTrips()
    {
        try {
            $trips = $this->tripApiUseCase->invitationTrips();
            return ApiResponse::sendResponse(200, __('app.trips-retrieved-successfully'), $trips);
        } catch (\Exception $e) {
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
            return ApiResponse::sendResponse(200, __('app.the-status-change-successfully'), []);
        } catch (\Exception $e) {
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $e->getMessage());
        }
    }

    public function tags()
    {
        try {
            $tags = $this->tripApiUseCase->tags();
            return ApiResponse::sendResponse(200, __('app.tags-retrieved-successfully'), $tags);
        } catch (\Exception $e) {
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $e->getMessage());
        }
    }

    public function create(CreateTripRequest $request)
    {
        try {
            $createTrip = $this->tripApiUseCase->createTrip($request);
            return ApiResponse::sendResponse(200, __('app.trip-created-successfully'), $createTrip);
        } catch (\Exception $e) {
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $e->getMessage());
        }
    }

    public function join(Request $request)
    {
        $id = $request->trip_id;

        $validator = Validator::make(['trip_id' => $id], [
            'trip_id' => ['required', 'exists:trips,id', new CheckAgeGenderExistenceRule()],
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $errors);
        }

        try {
            $this->tripApiUseCase->joinTrip($id);
            return ApiResponse::sendResponse(200, __('app.you-join-to-trip-successfully'), []);
        } catch (\Exception $e) {
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $e->getMessage());
        }
    }

    public function cancelJoin(Request $request)
    {
        $id = $request->trip_id;

        $validator = Validator::make(['trip_id' => $id], [
            'trip_id' => ['required', 'exists:trips,id', new CheckUserTripExistsRule()],
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $errors);
        }

        try {
            $this->tripApiUseCase->cancelJoinTrip($id, $request);
            return ApiResponse::sendResponse(200, __('app.you-are-left-from-the-trip-successfully'), []);
        } catch (\Exception $e) {
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $e->getMessage());
        }
    }

    public function privateTrips()
    {
        try {
            $trips = $this->tripApiUseCase->privateTrips();
            return ApiResponse::sendResponse(200, __('app.trips-retrieved-successfully'), $trips);
        } catch (\Exception $e) {
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $e->getMessage());
        }
    }

    public function tripDetails(Request $request)
    {
        $id = $request->trip_id;
        $validator = Validator::make(['trip_id' => $id], [
            'trip_id' => ['required', 'exists:trips,id'],
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $errors);
        }
        try {
            $details = $this->tripApiUseCase->tripDetails($request->trip_id);
            return ApiResponse::sendResponse(200, __('app.trips-details-retrieved-successfully'), $details);
        } catch (\Exception $e) {
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
            return ApiResponse::sendResponse(200, __('app.the-status-change-successfully'), []);
        } catch (\Exception $e) {
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $e->getMessage());
        }
    }

    public function favorite(Request $request)
    {
        $id = $request->trip_id;
        $validator = Validator::make(['trip_id' => $id], [
            'trip_id' => ['required', 'exists:trips,id', new CheckIfExistsInFavoratblesRule('App\Models\Trip')],
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $errors);
        }
        try {
            $trip = $this->tripApiUseCase->favorite($id);
            return ApiResponse::sendResponse(200, __('app.you-add-trip-in-favorite-successfully'), $trip);
        } catch (\Exception $e) {
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST, $e->getMessage());
        }
    }

    public function deleteFavorite(Request $request)
    {
        $id = $request->trip_id;
        $validator = Validator::make(['trip_id' => $id], [
            'trip_id' => ['required', 'exists:trips,id', new CheckIfNotExistsInFavoratblesRule('App\Models\Trip')],
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $errors);
        }
        try {
            $trip = $this->tripApiUseCase->deleteFavorite($id);
            return ApiResponse::sendResponse(200, __('app.you-deleted-the-trip-from-favorite-successfully'), $trip);
        } catch (\Exception $e) {
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST, $e->getMessage());
        }
    }

    public function addReview(Request $request)
    {
        $validator = Validator::make([
            'trip_id' => $request->trip_id,
            'rating' => $request->rating,
            'comment' => $request->comment
        ], [
            'trip_id' => ['required', 'exists:trips,id', new CheckIfExistsInReviewsRule('App\Models\Trip'), new CheckIfUserAttendanceAndPastTripRule()],
            'rating' => ['required', 'numeric'],
            'comment' => ['nullable', 'string']
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $errors);
        }
        try {
            $trip = $this->tripApiUseCase->addReview($validator->validated());
            return ApiResponse::sendResponse(200, __('app.you-add-review-in-trip-successfully'), $trip);
        } catch (\Exception $e) {
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST, $e->getMessage());
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
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $e->getMessage());
        }
    }

    public function remove(Request $request)
    {
        $validator = Validator::make(['trip_id' => $request->trip_id], [
            'trip_id' => ['required', 'exists:trips,id'],
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $errors);
        }

        try {
            $this->tripApiUseCase->remove($request->trip_id);
            // Send Notification For All Users In This Trip
            return ApiResponse::sendResponse(200, __('app.the-trip-deleted-successfully'), []);
        } catch (\Exception $e) {
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $e->getMessage());
        }
    }

    public function update(UpdateTripRequest $request)
    {
        $validator = Validator::make(['trip_id' => $request->trip_id], [
            'trip_id' => ['required', 'exists:trips,id', new CheckIfCanUpdateTripRule],
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $errors);
        }

        try {
            $this->tripApiUseCase->update($request);
            return ApiResponse::sendResponse(200, __('app.the-trip-updated-successfully'), []);
        } catch (\Exception $e) {
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
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $e->getMessage());
        }
    }

    public function removeUser(Request $request)
    {
        $validator = Validator::make(['trip_id' => $request->trip_id, 'user_id' => $request->user_id], [
            'trip_id' => ['required', 'exists:trips,id'],
            'user_id' => ['required', 'exists:users,id'],
        ]);
        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $errors);
        }
        try {
            $this->tripApiUseCase->removeUser($request);
            return ApiResponse::sendResponse(200, __('app.the-user-deleted-successfully'), []);
        } catch (\Exception $e) {
            dd($e);
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $e->getMessage());
        }
    }
}

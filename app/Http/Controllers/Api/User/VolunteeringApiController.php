<?php

namespace App\Http\Controllers\Api\User;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\User\Event\DayRequest;
use App\Rules\CheckIfExistsInFavoratblesRule;
use App\Rules\CheckIfExistsInReviewsRule;
use App\Rules\CheckIfExistsInToUpdateReviewsRule;
use App\Rules\CheckIfNotExistsInFavoratblesRule;
use App\Rules\CheckIfPastEventOrVolunteering;
use App\Rules\CheckUserInterestExistsRule;
use App\Rules\CheckUserInterestRule;
use App\UseCases\Api\User\VolunteeringApiUseCase;
use App\UseCases\Web\Admin\VolunteeringUseCase;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class VolunteeringApiController extends Controller
{
    protected $volunteeringApiUseCase;

    public function __construct(VolunteeringApiUseCase $volunteeringApiUseCase)
    {

        $this->volunteeringApiUseCase = $volunteeringApiUseCase;
    }

    public function index()
    {
        try {
            $volunteering = $this->volunteeringApiUseCase->allVolunteerings();
            return ApiResponse::sendResponse(200, 'Volunteering Retrieved Successfully', $volunteering);
        } catch (\Exception $e) {
            return ApiResponse::sendResponse(Response::HTTP_BAD_REQUEST, __("validation.api.something-went-wrong"), $e->getMessage());
        }
    }

    public function activeVolunteerings()
    {
        try {
            $volunteering = $this->volunteeringApiUseCase->activeVolunteerings();
            return ApiResponse::sendResponse(200, 'Active Volunteering Retrieved Successfully', $volunteering);
        } catch (\Exception $e) {
            return ApiResponse::sendResponse(Response::HTTP_BAD_REQUEST, __("validation.api.something-went-wrong"), $e->getMessage());
        }
    }

    public function volunteering(Request $request)
    {
        $slug = $request->volunteering_slug;
        $validator = Validator::make(['volunteering_slug' => $slug], [
            'volunteering_slug' => 'required|exists:volunteerings,slug',
        ]);

        if ($validator->fails()) {
            return ApiResponse::sendResponse(Response::HTTP_BAD_REQUEST, __("validation.api.something-went-wrong"), $validator->errors());
        }
        $data = $validator->validated();
        try {
            $volunteering = $this->volunteeringApiUseCase->Volunteering($data['volunteering_slug']);
            return ApiResponse::sendResponse(200, 'Active Volunteering Retrieved Successfully', $volunteering);
        } catch (\Exception $e) {
            return ApiResponse::sendResponse(Response::HTTP_BAD_REQUEST, __("validation.api.something-went-wrong"), $e->getMessage());
        }
    }

    public function dateVolunteering(DayRequest $request)
    {

        try {
            $volunteering = $this->volunteeringApiUseCase->dateVolunteerings($request->validated());
            return ApiResponse::sendResponse(200, ' Volunteering of specific date Retrieved Successfully', $volunteering);
        } catch (\Exception $e) {
            return ApiResponse::sendResponse(Response::HTTP_BAD_REQUEST, __("validation.api.something-went-wrong"), $e->getMessage());
        }
    }

    public function interest(Request $request)
    {

        $id = $request->volunteering_id;
        $validator = Validator::make(['volunteering_id' => $id], [
            'volunteering_id' =>  ['required', 'exists:volunteerings,id', new CheckUserInterestRule('App\Models\Volunteering')],
        ]);


        if ($validator->fails()) {
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $validator->errors()->messages()['volunteering_id'][0]);
        }
        try {
            $events = $this->volunteeringApiUseCase->interestVolunteering($id);
            return ApiResponse::sendResponse(200, 'You Add Volunteering in Interest  Successfully', $events);
        } catch (\Exception $e) {
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST, $e->getMessage());
        }
    }

    public function disinterest(Request $request)
    {
        $id = $request->volunteering_id;
        $validator = Validator::make(['volunteering_id' => $id], [
            'volunteering_id' => ['required', 'exists:volunteerings,id', new CheckUserInterestExistsRule('App\Models\Volunteering')],
        ]);


        if ($validator->fails()) {
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $validator->errors()->messages()['volunteering_id'][0]);
        }
        try {
            $events = $this->volunteeringApiUseCase->disinterestVolunteering($id);
            return ApiResponse::sendResponse(200, 'You delete volunteering in Interest  Successfully', $events);
        } catch (\Exception $e) {
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST, $e->getMessage());
        }
    }

    public function favorite(Request $request)
    {
        $id = $request->volunteering_id;
        $validator = Validator::make(['volunteering_id' => $id], [
            'volunteering_id' => ['required', 'exists:volunteerings,id', new CheckIfExistsInFavoratblesRule('App\Models\Volunteering')],
        ]);


        if ($validator->fails()) {
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $validator->errors()->messages()['volunteering_id'][0]);
        }
        try {
            $events = $this->volunteeringApiUseCase->favorite($id);
            return ApiResponse::sendResponse(200, 'You Add volunteering in favorite  Successfully', $events);
        } catch (\Exception $e) {
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST, $e->getMessage());
        }
    }

    public function deleteFavorite(Request $request)
    {
        $id = $request->volunteering_id;
        $validator = Validator::make(['volunteering_id' => $id], [
            'volunteering_id' => ['required', 'exists:volunteerings,id', new CheckIfNotExistsInFavoratblesRule('App\Models\Volunteering')],
        ]);


        if ($validator->fails()) {
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $validator->errors()->messages()['volunteering_id'][0]);
        }
        try {
            $events = $this->volunteeringApiUseCase->deleteFavorite($id);
            return ApiResponse::sendResponse(200, 'You delete volunteering from favorite Successfully', $events);
        } catch (\Exception $e) {
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST, $e->getMessage());
        }
    }

    public function addReview(Request $request)
    {
        $validator = Validator::make([
            'volunteering_id' => $request->volunteering_id,
            'rating' => $request->rating,
            'comment' => $request->comment
        ], [
            'volunteering_id' => ['required', 'exists:volunteerings,id', new CheckIfExistsInReviewsRule('App\Models\Volunteering'), new CheckIfPastEventOrVolunteering('App\Models\Volunteering')],
            'rating' => ['required', 'numeric'],
            'comment' => ['nullable', 'string']
        ]);

        if ($validator->fails()) {
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $validator->errors()->messages());
        }

        try {
            $volunteering = $this->volunteeringApiUseCase->addReview($validator->validated());
            return ApiResponse::sendResponse(200, 'You Add review in Volunteering Successfully', $volunteering);
        } catch (\Exception $e) {
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST, $e->getMessage());
        }
    }

    public function updateReview(Request $request)
    {

        $validator = Validator::make([
            'volunteering_id' => $request->volunteering_id,
            'rating' => $request->rating,
            'comment' => $request->comment
        ], [
            'volunteering_id' => ['required', 'exists:volunteerings,id', new CheckIfExistsInToUpdateReviewsRule('App\Models\Volunteering')],
            'rating' => ['required', 'numeric'],
            'comment' => ['nullable', 'string']
        ]);


        if ($validator->fails()) {
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $validator->errors()->messages());
        }
        try {
            $volunteering = $this->volunteeringApiUseCase->updateReview($validator->validated());
            return ApiResponse::sendResponse(200, 'You update review in Volunteering Successfully', $volunteering);
        } catch (\Exception $e) {
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST, $e->getMessage());
        }
    }

    public function deleteReview(Request $request)
    {
        $validator = Validator::make([
            'volunteering_id' => $request->volunteering_id,
        ], [
            'volunteering_id' => ['required', 'exists:volunteerings,id', new CheckIfExistsInToUpdateReviewsRule('App\Models\Volunteering')],
        ]);

        if ($validator->fails()) {
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $validator->errors()->messages());
        }
        try {
            $event = $this->volunteeringApiUseCase->deleteReview($validator->validated());
            return ApiResponse::sendResponse(200, 'You delete the review in Volunteering Successfully', $event);
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
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $validator->errors()->messages());
        }

        try {
            $this->volunteeringApiUseCase->reviewsLike($request);
            return ApiResponse::sendResponse(200, __('app.the-status-change-successfully'), []);
        } catch (\Exception $e) {
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $e->getMessage());
        }
    }

    public function search(Request $request)
    {
        $query = $request->input('query');
        try {
            $places = $this->volunteeringApiUseCase->search($query);
            return ApiResponse::sendResponse(200, __('app.the-searched-volunteering-retrieved-successfully'), $places);
        } catch (\Exception $e) {
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $e->getMessage());
        }
    }

    public function interestedList()
    {
        try {
            $id = Auth::guard('api')->user()->id;
            $validator = Validator::make(['id' => $id], [
                'id' => ['required', 'exists:users,id'],
            ]);

            if ($validator->fails()) {
                return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $validator->errors()->messages());
            }

            $volunteering = $this->volunteeringApiUseCase->interestedList($id);
            return ApiResponse::sendResponse(200, 'Volunteering Retrieved Successfully', $volunteering);
        } catch (\Exception $e) {
            return ApiResponse::sendResponse(Response::HTTP_BAD_REQUEST, __("validation.api.something-went-wrong"), $e->getMessage());
        }
    }
}

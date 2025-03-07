<?php

namespace App\Http\Controllers\Api\User;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\User\Place\FilterPlaceRequest;
use App\Models\Place;
use App\Rules\CheckIfExistsInReviewsRule;
use App\Rules\CheckIfExistsInFavoratblesRule;
use App\Rules\CheckIfExistsInVistedPlaceTableRule;
use App\Rules\CheckIfNotExistsInFavoratblesRule;
use App\Rules\CheckIfExistsInToUpdateReviewsRule;
use App\Rules\CheckIfNotExistsInVistedPlaceTableRule;
use App\UseCases\Api\User\PlaceApiUseCase;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class PlaceApiController extends Controller
{
    protected $placeApiUseCase;

    public function __construct(PlaceApiUseCase $placeApiUseCase)
    {

        $this->placeApiUseCase = $placeApiUseCase;
    }
    /**
     * Display a listing of the resource.
     */

    public function singlePlaces(Request $request)
    {
        $id = $request->place_id;

        $validator = Validator::make(['place_id' => $id], [
            'place_id' => 'required|exists:places,id',
        ],[
            'place_id.exists'=>__('validation.api.place-id-invalid'),
            'place_id.required'=>__('validation.api.place-id-does-not-exists')
        ]);

        if ($validator->fails()) {
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $validator->errors()->messages()['place_id']);
        }
        try {
            $allPlaces = $this->placeApiUseCase->singlePlace($id);

            return ApiResponse::sendResponse(200, __('app.place.api.place-retrieved-by-id-successfully'), $allPlaces);
        } catch (\Exception $e) {
            return ApiResponse::sendResponse(Response::HTTP_BAD_REQUEST, __("validation.api.something-went-wrong"), $e->getMessage());
        }
    }

    public function createFavoritePlace(Request $request)
    {
        $id = $request->place_id;

        $validator = Validator::make(['place_id' => $id], [
            'place_id' => ['required', 'exists:places,id', new CheckIfExistsInFavoratblesRule('App\Models\Place')],
        ],[
            'place_id.exists'=>__('validation.api.place-id-invalid'),
            'place_id.required'=>__('validation.api.place-id-does-not-exists')
        ]);

        if ($validator->fails()) {
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $validator->errors()->messages()['place_id'][0]);
        }

        try {
            $createFavPlace = $this->placeApiUseCase->createFavoritePlace($id);

            return ApiResponse::sendResponse(200,  __('app.place.api.you-put-this-place-in-favorite-list'), $createFavPlace);
        } catch (\Exception $e) {
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $e->getMessage());
        }
    }

    public  function deleteFavoritePlace(Request $request)
    {
        $id = $request->place_id;

        $validator = Validator::make(['place_id' => $id], [
            'place_id' => ['required', 'exists:places,id', new CheckIfNotExistsInFavoratblesRule('App\Models\Place')],
        ],[
            'place_id.exists'=>__('validation.api.place-id-invalid'),
            'place_id.required'=>__('validation.api.place-id-does-not-exists')
        ]);

        if ($validator->fails()) {
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $validator->errors()->messages()['place_id']);
        }

        try {
            $deleteFavPlace = $this->placeApiUseCase->deleteFavoritePlace($id);
            return ApiResponse::sendResponse(200, __('app.place.api.you-remove-this-place-in-favorite-list'), $deleteFavPlace);
        } catch (\Exception $e) {
            return ApiResponse::sendResponse(Response::HTTP_BAD_REQUEST, __("validation.api.something-went-wrong"), $e->getMessage());
        }
    }

    public function createVisitedPlace(Request $request)
    {
        $id = $request->place_id;

        $validator = Validator::make(['place_id' => $id], [
            'place_id' => ['required', 'exists:places,id', new CheckIfExistsInVistedPlaceTableRule()],
        ],[
            'place_id.exists'=>__('validation.api.place-id-invalid'),
            'place_id.required'=>__('validation.api.place-id-does-not-exists')
        ]);

        if ($validator->fails()) {
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $validator->errors()->messages()['place_id']);
        }

        try {
            $createVisitedPlace = $this->placeApiUseCase->createVisitedPlace($id);

            return ApiResponse::sendResponse(200, __('app.place.api.you-put-this-place-in-visited-place-list'), $createVisitedPlace);
        } catch (\Exception $e) {
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $e->getMessage());
        }
    }


    public  function deleteVisitedPlace(Request $request)
    {
        $id = $request->place_id;

        $validator = Validator::make(['place_id' => $id], [
            'place_id' => ['required', 'exists:places,id', new CheckIfNotExistsInVistedPlaceTableRule()],
        ],[
            'place_id.exists'=>__('validation.api.place-id-invalid'),
            'place_id.required'=>__('validation.api.place-id-does-not-exists')
        ]);

        if ($validator->fails()) {
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $validator->errors()->messages()['place_id']);
        }

        try {
            $deleteVisitedPlace = $this->placeApiUseCase->deleteVisitedPlace($id);
            return ApiResponse::sendResponse(200, __('app.place.api.remove-place-form-visited-places-list-successfully'), $deleteVisitedPlace);
        } catch (\Exception $e) {
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST, $e->getMessage());
        }
    }

    public function addReview(Request $request)
    {
        $validator = Validator::make([
            'place_id' => $request->place_id,
            'rating' => $request->rating,
            'comment' => $request->comment
        ], [
            'place_id' => ['required', 'exists:places,id', new CheckIfExistsInReviewsRule('App\Models\Place')],
            'rating' => ['required', 'numeric'],
            'comment' => ['nullable', 'string']
        ],
            [
            'place_id.exists'=>__('validation.api.place-id-invalid'),
            'place_id.required'=>__('validation.api.place-id-does-not-exists'),
            'rating.required' => __('validation.api.rating-is-required'),
            'comment.string'=>__('validation.api.comment-should-be-string'),

        ]
        );

        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $errors);
        }

        try {
            $placeRev = $this->placeApiUseCase->addReview($validator->validated());
            return ApiResponse::sendResponse(200, __('app.place.api.you-added-review-for-this-place-successfully'), $placeRev);
        } catch (\Exception $e) {
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST, $e->getMessage());
        }
    }

    public function updateReview(Request $request)
    {

        $validator = Validator::make([
            'place_id' => $request->place_id,
            'rating' => $request->rating,
            'comment' => $request->comment
        ], [
            'place_id' => ['required', 'exists:places,id', new CheckIfExistsInToUpdateReviewsRule('App\Models\Place')],
            'rating' => ['required', 'numeric'],
            'comment' => ['nullable', 'string']
        ],
            [
                'place_id.exists'=>__('validation.api.place-id-invalid'),
                'place_id.required'=>__('validation.api.place-id-does-not-exists'),
                'rating.required' => __('validation.api.rating-is-required'),
                'comment.string'=>__('validation.api.comment-should-be-string'),

            ]
        );


        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $errors);
        }
        try {
            $placeRev = $this->placeApiUseCase->updateReview($validator->validated());
            return ApiResponse::sendResponse(200,  __('app.place.api.you-updated-review-for-this-place-successfully'), $placeRev);
        } catch (\Exception $e) {
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST, $e->getMessage());
        }
    }

    public function deleteReview(Request $request)
    {
        $validator = Validator::make([
            'place_id' => $request->place_id,
        ], [
            'place_id' => ['required', 'exists:places,id', new CheckIfExistsInToUpdateReviewsRule('App\Models\Place')],
        ],[
            'place_id.exists'=>__('validation.api.place-id-invalid'),
            'place_id.required'=>__('validation.api.place-id-does-not-exists')
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $errors);
        }
        try {
            $placeRev = $this->placeApiUseCase->deleteReview($validator->validated());
            return ApiResponse::sendResponse(200,  __('app.place.api.you-remove-review-for-this-place-successfully'), $placeRev);
        } catch (\Exception $e) {
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
            $this->placeApiUseCase->reviewsLike($request);
            return ApiResponse::sendResponse(200,  __('app.event.api.the-likable-status-change-successfully'), []);
        } catch (\Exception $e) {
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $e->getMessage());
        }
    }

    public function search(Request $request)
    {
        $query = $request->input('query');
        try {
            $places = $this->placeApiUseCase->search($query);
            return ApiResponse::sendResponse(200, __('app.place.api.the-searched-place-retrieved-successfully'), $places);
        } catch (\Exception $e) {
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $e->getMessage());
        }
    }

    public function filter(FilterPlaceRequest $request)
    {
        try {
            $places = $this->placeApiUseCase->filter($request->validated());
            return ApiResponse::sendResponse(200,  __('app.place.api.the-searched-place-retrieved-successfully'), $places);
        } catch (\Exception $e) {
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $e->getMessage());
        }
    }

    public function allSearch(Request $request)
    {
        $query = $request->input('query');
        try {
            $places = $this->placeApiUseCase->allSearch($query);
            return ApiResponse::sendResponse(200, __('app.place.api.the-searched-place-retrieved-successfully'), $places);
        } catch (\Exception $e) {
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $e->getMessage());
        }

    }

}

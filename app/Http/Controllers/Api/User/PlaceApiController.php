<?php

namespace App\Http\Controllers\Api\User;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\User\Place\FilterPlaceRequest;
use App\Rules\ActivePlaceRule;
use App\Rules\CheckIfExistsInVistedPlaceTableRule;
use App\Rules\CheckIfExistsInToUpdateReviewsRule;
use App\Rules\CheckIfNotExistsInVistedPlaceTableRule;
use App\UseCases\Api\User\PlaceApiUseCase;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class PlaceApiController extends Controller
{
    public function __construct(protected PlaceApiUseCase $placeApiUseCase)
    {
        $this->placeApiUseCase = $placeApiUseCase;
    }

    public function singlePlaces(Request $request)
    {
        $slug = $request->place_slug;

        $validator = Validator::make(['place_slug' => $slug], [
            'place_slug' => ['bail', 'required', 'exists:places,slug', new ActivePlaceRule()],

        ], [
            'place_slug.exists' => __('validation.api.place-id-invalid'),
            'place_slug.required' => __('validation.api.place-id-does-not-exists')
        ]);

        if ($validator->fails()) {
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $validator->errors()->messages()['place_slug']);
        }
        try {
            $allPlaces = $this->placeApiUseCase->singlePlace($slug);

            return ApiResponse::sendResponse(200, __('app.api.place-retrieved-by-id-successfully'), $allPlaces);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);
            return ApiResponse::sendResponse(Response::HTTP_BAD_REQUEST, __("validation.api.something-went-wrong"), $e->getMessage());
        }
    }


    public function createVisitedPlace(Request $request, $slug)
    {
        $validator = Validator::make(['slug' => $slug], [
            'slug' => ['required', 'exists:places,slug', new ActivePlaceRule(), new CheckIfExistsInVistedPlaceTableRule()],
        ], [
            'slug.exists' => __('validation.api.place-id-invalid'),
            'slug.required' => __('validation.api.place-id-does-not-exists')
        ]);

        if ($validator->fails()) {
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $validator->errors()->messages()['slug']);
        }

        try {
            $createVisitedPlace = $this->placeApiUseCase->createVisitedPlace($validator->validated()['slug']);

            return ApiResponse::sendResponse(200, __('app.place.api.you-put-this-place-in-visited-place-list'), $createVisitedPlace);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $e->getMessage());
        }
    }


    public  function deleteVisitedPlace(Request $request, $slug)
    {

        $validator = Validator::make(['slug' => $slug], [
            'slug' => ['required', 'exists:places,slug', new ActivePlaceRule(), new CheckIfNotExistsInVistedPlaceTableRule()],
        ], [
            'slug.exists' => __('validation.api.place-id-invalid'),
            'slug.required' => __('validation.api.place-id-does-not-exists')
        ]);

        if ($validator->fails()) {
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $validator->errors()->messages()['slug']);
        }

        try {
            $deleteVisitedPlace = $this->placeApiUseCase->deleteVisitedPlace($validator->validated()['slug']);
            return ApiResponse::sendResponse(200, __('app.place.api.remove-place-form-visited-places-list-successfully'), $deleteVisitedPlace);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST, $e->getMessage());
        }
    }

    public function updateReview(Request $request)
    {

        $validator = Validator::make(
            [
                'place_id' => $request->place_id,
                'rating' => $request->rating,
                'comment' => $request->comment
            ],
            [
                'place_id' => ['required', 'exists:places,id', new CheckIfExistsInToUpdateReviewsRule('App\Models\Place')],
                'rating' => ['required', 'numeric'],
                'comment' => ['nullable', 'string']
            ],
            [
                'place_id.exists' => __('validation.api.place-id-invalid'),
                'place_id.required' => __('validation.api.place-id-does-not-exists'),
                'rating.required' => __('validation.api.rating-is-required'),
                'comment.string' => __('validation.api.comment-should-be-string'),

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
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST, $e->getMessage());
        }
    }

    public function deleteReview(Request $request)
    {
        $validator = Validator::make([
            'place_id' => $request->place_id,
        ], [
            'place_id' => ['required', 'exists:places,id', new CheckIfExistsInToUpdateReviewsRule('App\Models\Place')],
        ], [
            'place_id.exists' => __('validation.api.place-id-invalid'),
            'place_id.required' => __('validation.api.place-id-does-not-exists')
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $errors);
        }
        try {
            $placeRev = $this->placeApiUseCase->deleteReview($validator->validated());
            return ApiResponse::sendResponse(200,  __('app.place.api.you-remove-review-for-this-place-successfully'), $placeRev);
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
                'review_id.required' => __('validation.api.the-review-id-required'),
                'status' => __('validation.api.the-status-required')
            ]
        );

        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $errors);
        }

        try {
            $this->placeApiUseCase->reviewsLike($request);
            return ApiResponse::sendResponse(200,  __('app.event.api.the-likable-status-change-successfully'), []);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);
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
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $e->getMessage());
        }
    }

    public function filter(FilterPlaceRequest $request)
    {
        try {
            $places = $this->placeApiUseCase->filter($request->validated());
            return ApiResponse::sendResponse(200,  __('app.place.api.the-searched-place-retrieved-successfully'), $places);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);
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
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $e->getMessage());
        }
    }
}

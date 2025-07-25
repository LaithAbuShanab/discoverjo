<?php

namespace App\Http\Controllers\Api\User;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Rules\CheckIfExistsInReviewsRule;
use App\Rules\CheckIfNotExistsInReviewsRule;
use App\Rules\CheckIfReviewOwnerActiveRule;
use App\Rules\CheckIfTypeAndSlugRule;
use App\Rules\CheckIfTypeIsInThePastRule;
use App\Rules\CheckIfUserTypeActiveRule;
use App\Rules\NotBlockedUserRule;
use App\UseCases\Api\User\ReviewApiUseCase;

class ReviewApiController extends Controller
{
    public function __construct(protected ReviewApiUseCase $reviewApiUseCase)
    {
        $this->reviewApiUseCase = $reviewApiUseCase;
    }

    public function reviews(Request $request, $type, $slug)
    {
        $validator = Validator::make(
            [
                'type' => $type,
                'slug' => $slug
            ],
            [

                'type' => ['bail', 'required', Rule::in(['place', 'trip', 'event', 'volunteering', 'guideTrip', 'service', 'property'])],
                'slug' => ['bail', 'required', new CheckIfTypeAndSlugRule(), new CheckIfUserTypeActiveRule()],
            ],
            [
                'slug.required' => __('validation.api.id-does-not-exists'),
                'type.in' => __('validation.api.not-acceptable-type'),
            ]
        );

        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $errors);
        }
        try {
            $review = $this->reviewApiUseCase->allReviews($validator->validated());
            return ApiResponse::sendResponse(200, __('app.api.reviews-retrieved-successfully'), $review);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST, $e->getMessage());
        }
    }

    public function addReview(Request $request, $type, $slug)
    {
        $validator = Validator::make([
            'type' => $type,
            'slug' => $slug,
            'rating' => $request->rating,
            'comment' => $request->comment
        ], [
            'type' => ['bail', 'required', Rule::in(['place', 'trip', 'event', 'volunteering', 'guideTrip', 'service', 'property'])],
            'slug' => ['bail', 'required', new CheckIfTypeAndSlugRule(), new CheckIfExistsInReviewsRule(), new CheckIfTypeIsInThePastRule(), new CheckIfUserTypeActiveRule()],
            'rating' => ['required', 'numeric', 'min:1', 'max:5', 'integer'],
            'comment' => ['nullable', 'string']
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $errors);
        }

        try {
            $review = $this->reviewApiUseCase->addReview($validator->validated());
            return ApiResponse::sendResponse(200, __('app.api.you-added-review-successfully'), $review);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST, $e->getMessage());
        }
    }

    public function updateReview(Request $request, $type, $slug)
    {

        $validator = Validator::make([
            'type' => $type,
            'slug' => $slug,
            'rating' => $request->rating,
            'comment' => $request->comment
        ], [
            'type' => ['bail', 'required', Rule::in(['place', 'trip', 'event', 'volunteering', 'guideTrip', 'service', 'property'])],
            'slug' => ['required', new CheckIfTypeAndSlugRule(), new CheckIfNotExistsInReviewsRule()],
            'rating' => ['required', 'numeric', 'min:1', 'max:5', 'integer'],
            'comment' => ['nullable', 'string']
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $errors);
        }

        try {
            $event = $this->reviewApiUseCase->updateReview($validator->validated());
            return ApiResponse::sendResponse(200, __('app.api.your-review-updated-successfully'), $event);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST, $e->getMessage());
        }
    }

    public function deleteReview(Request $request, $type, $slug)
    {
        $validator = Validator::make([
            'type' => $type,
            'slug' => $slug,
        ], [
            'type' => ['bail', 'required', Rule::in(['place', 'trip', 'event', 'volunteering', 'guideTrip', 'service', 'property'])],
            'slug' => ['required', new CheckIfTypeAndSlugRule(), new CheckIfNotExistsInReviewsRule()],
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $errors);
        }
        try {
            $event = $this->reviewApiUseCase->deleteReview($validator->validated());
            return ApiResponse::sendResponse(200, __('app.api.you-deleted-your-review-successfully'), $event);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST, $e->getMessage());
        }
    }

    public function likeDislike(Request $request, $status, $review_id)
    {
        $validator = Validator::make(
            [
                'status' => $status,
                'review_id' => $review_id,
            ],
            [
                'status' => ['required', Rule::in(['like', 'dislike'])],
                'review_id' => [
                    'bail',
                    'required',
                    'integer',
                    'exists:reviewables,id',
                    new CheckIfReviewOwnerActiveRule(),
                    new NotBlockedUserRule(\App\Models\Reviewable::class),
                ],
            ],
            [
                'review_id.exists' => __('validation.api.the-selected-review-id-does-not-exists'),
                'review_id.integer' => __('validation.api.the-review-id-must-be-integer'),
                'review_id.required' => __('validation.api.the-review-id-required'),
                'status' => __('validation.api.the-status-required'),
                'status.in' => __('validation.api.not-acceptable-status'),
            ]
        );

        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $errors);
        }

        try {
            $this->reviewApiUseCase->reviewsLike($validator->validated());
            return ApiResponse::sendResponse(200, __('app.api.the-likable-status-change-successfully'), []);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $e->getMessage());
        }
    }
}

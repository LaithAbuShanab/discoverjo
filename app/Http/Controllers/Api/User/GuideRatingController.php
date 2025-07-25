<?php

namespace App\Http\Controllers\Api\User;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\User\GuideRating\CreateGuideRatingRequest;
use App\Http\Requests\Api\User\GuideRating\UpdateGuideRatingRequest;
use App\Rules\CheckIfTheIdIsGuideRule;
use App\Rules\CheckIfUserActiveRule;
use App\Rules\CheckIfUserJoinedGuidPreviouslyRule;
use App\Rules\CheckIfUserMadeRatingRule;
use App\Rules\CheckIfUserMakeRatingOnGuideRule;
use App\Rules\CheckIfUserMakeUpdateToUpdateRule;
use App\Rules\CheckIfUserNotGuideForRatingRule;
use App\Rules\CurrentBlockUserRule;
use App\UseCases\Api\User\GuideRatingApiUseCase;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class GuideRatingController extends Controller
{
    public function __construct(protected GuideRatingApiUseCase $guideRatingApiUseCase) {}

    public function create(CreateGuideRatingRequest $request, $guide_slug)
    {
        $validator = Validator::make(
            ['guide_slug' => $guide_slug],
            [
                'guide_slug' => ['bail', 'required', 'exists:users,slug', new CurrentBlockUserRule(), new CheckIfTheIdIsGuideRule(), new CheckIfUserNotGuideForRatingRule(), new CheckIfUserJoinedGuidPreviouslyRule(), new CheckIfUserMakeRatingOnGuideRule(), new CheckIfUserActiveRule()],
            ],
            [
                'guide_slug.required' => __('validation.api.guide-id-required'),
                'guide_slug.exists' => __('validation.api.guide-id-does-not-exists'),
            ]
        );

        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $errors);
        }
        $validatedData = array_merge($request->validated(), $validator->validated());

        try {
            $this->guideRatingApiUseCase->createGuideRating($validatedData);
            return ApiResponse::sendResponse(200, __('app.api.rating-created-successfully'), []);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $e->getMessage());
        }
    }

    public function update(UpdateGuideRatingRequest $request, $guide_slug)
    {
        $validator = Validator::make(
            ['guide_slug' => $guide_slug],
            [
                'guide_slug' => ['bail', 'required', 'exists:users,slug', new CurrentBlockUserRule(), new CheckIfTheIdIsGuideRule(), new CheckIfUserNotGuideForRatingRule(), new CheckIfUserMakeUpdateToUpdateRule(), new CheckIfUserActiveRule()],
            ],
            [
                'guide_slug.required' => __('validation.api.guide-id-required'),
                'guide_slug.exists' => __('validation.api.guide-id-does-not-exists'),
            ]
        );

        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $errors);
        }
        $validatedData = array_merge($request->validated(), $validator->validated());
        try {
            $this->guideRatingApiUseCase->updateGuideRating($validatedData);
            return ApiResponse::sendResponse(200,  __('app.api.rating-updated-successfully'), []);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $e->getMessage());
        }
    }

    public function delete(Request $request, $guide_slug)
    {
        $validator = Validator::make(['guide_slug' => $guide_slug], [
            'guide_slug' => ['bail', 'required', 'exists:users,slug', new CheckIfTheIdIsGuideRule(), new CheckIfUserMadeRatingRule(), new CheckIfUserActiveRule()],
        ], [
            'guide_slug.required' => __('validation.api.guide-id-required'),
            'guide_slug.exists' => __('validation.api.guide-id-does-not-exists'),
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $errors);
        }
        try {
            $this->guideRatingApiUseCase->deleteGuideRating($validator->validated()['guide_slug']);
            return ApiResponse::sendResponse(200,  __('app.api.rating-deleted-successfully'), []);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $e->getMessage());
        }
    }

    public function show(Request $request, $guide_slug)
    {
        $validator = Validator::make(['guide_slug' => $guide_slug], [
            'guide_slug' => ['bail', 'required', 'exists:users,slug', new CheckIfTheIdIsGuideRule(), new CheckIfUserMadeRatingRule(), new CheckIfUserActiveRule()],
        ], [
            'guide_slug.required' => __('validation.api.guide-id-required'),
            'guide_slug.exists' => __('validation.api.guide-id-does-not-exists'),
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $errors);
        }
        try {
            $rating = $this->guideRatingApiUseCase->showGuideRating($validator->validated()['guide_slug']);
            return ApiResponse::sendResponse(200,  __('app.api.rating-retrieved-successfully'), $rating);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $e->getMessage());
        }
    }
}

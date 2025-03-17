<?php

namespace App\Http\Controllers\Api\User;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\User\GuideRating\CreateGuideRatingRequest;
use App\Http\Requests\Api\User\GuideRating\DeleteGuideRatingRequest;
use App\Http\Requests\Api\User\GuideRating\UpdateGuideRatingRequest;
use App\Rules\CheckIfTheIdIsGuideRule;
use App\Rules\CheckIfUserJoinedGuidPreviouslyRule;
use App\Rules\CheckIfUserMadeRatingRule;
use App\Rules\CheckIfUserMakeRatingOnGuideRule;
use App\Rules\CheckIfUserMakeUpdateToUpdateRule;
use App\Rules\CheckIfUserNotGuideForRatingRule;
use App\UseCases\Api\User\GuideRatingApiUseCase;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class GuideRatingController extends Controller
{
    protected $guideRatingApiUseCase;

    public function __construct(GuideRatingApiUseCase $guideRatingUseCase) {

        $this->guideRatingApiUseCase = $guideRatingUseCase;
    }

    public function create(CreateGuideRatingRequest $request,$guide_slug)
    {
        $validator = Validator::make(['guide_slug' => $guide_slug], [
            'guide_slug'=>['bail','required','exists:users,slug',new CheckIfTheIdIsGuideRule(),new CheckIfUserNotGuideForRatingRule(),new CheckIfUserJoinedGuidPreviouslyRule(),new CheckIfUserMakeRatingOnGuideRule()],
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $errors);
        }
        $validatedData = array_merge($request->validated(), $validator->validated());

        try{
            $rating = $this->guideRatingApiUseCase->createGuideRating($validatedData);
            return ApiResponse::sendResponse(200, __('app.api.rating-created-successfully'), []);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $e->getMessage());
        }
    }

    public function update(UpdateGuideRatingRequest $request,$guide_slug)
    {
        $validator = Validator::make(['guide_slug' => $guide_slug], [
            'guide_slug'=>['bail','required','exists:users,slug',new CheckIfTheIdIsGuideRule(),new CheckIfUserNotGuideForRatingRule(),new CheckIfUserMakeUpdateToUpdateRule()],
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $errors);
        }
        $validatedData = array_merge($request->validated(), $validator->validated());
        try{
            $rating = $this->guideRatingApiUseCase->updateGuideRating($validatedData);
            return ApiResponse::sendResponse(200,  __('app.api.rating-updated-successfully'), []);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $e->getMessage());
        }
    }

    public function delete(Request $request,$guide_slug)
    {

        $validator = Validator::make(['guide_slug' => $guide_slug], [
            'guide_slug' => ['bail','required', 'exists:users,slug' ,new CheckIfTheIdIsGuideRule(),new CheckIfUserMadeRatingRule()],
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $errors);
        }
        try{
            $rating = $this->guideRatingApiUseCase->deleteGuideRating($validator->validated()['guide_slug']);
            return ApiResponse::sendResponse(200,  __('app.api.rating-deleted-successfully'), []);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $e->getMessage());
        }
    }

    public function show(Request $request,$guide_slug)
    {

        $validator = Validator::make(['guide_slug' => $guide_slug], [
            'guide_slug' => ['bail','required', 'exists:users,slug' ,new CheckIfTheIdIsGuideRule(),new CheckIfUserMadeRatingRule()],
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $errors);
        }
        try{
            $rating = $this->guideRatingApiUseCase->showGuideRating($validator->validated()['guide_slug']);
            return ApiResponse::sendResponse(200,  __('app.api.rating-retrieved-successfully'), $rating);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $e->getMessage());
        }

    }
}

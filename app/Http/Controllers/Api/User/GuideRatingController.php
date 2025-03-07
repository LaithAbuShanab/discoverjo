<?php

namespace App\Http\Controllers\Api\User;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\User\GuideRating\CreateGuideRatingRequest;
use App\Http\Requests\Api\User\GuideRating\DeleteGuideRatingRequest;
use App\Http\Requests\Api\User\GuideRating\UpdateGuideRatingRequest;
use App\Rules\CheckIfTheIdIsGuideRule;
use App\Rules\CheckIfUserMadeRatingRule;
use App\Rules\CheckIfUserMakeUpdateToUpdateRule;
use App\Rules\CheckIfUserNotGuideForRatingRule;
use App\UseCases\Api\User\GuideRatingApiUseCase;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

class GuideRatingController extends Controller
{
    protected $guideRatingApiUseCase;

    public function __construct(GuideRatingApiUseCase $guideRatingUseCase) {

        $this->guideRatingApiUseCase = $guideRatingUseCase;
    }

    public function create(CreateGuideRatingRequest $request)
    {
        try{
            $rating = $this->guideRatingApiUseCase->createGuideRating($request->validated());
            return ApiResponse::sendResponse(200, __('app.api.rating-created-successfully'), []);
        } catch (\Exception $e) {
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $e->getMessage());
        }
    }

    public function update(UpdateGuideRatingRequest $request)
    {
        try{
            $rating = $this->guideRatingApiUseCase->updateGuideRating($request->validated());
            return ApiResponse::sendResponse(200,  __('app.api.rating-updated-successfully'), []);
        } catch (\Exception $e) {
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $e->getMessage());
        }
    }

    public function delete(Request $request)
    {
        $id = $request->guide_id;

        $validator = Validator::make(['guide_id' => $id], [
            'guide_id' => ['required', 'exists:users,id' ,new CheckIfTheIdIsGuideRule(),new CheckIfUserMadeRatingRule()],
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $errors);
        }
        try{
            $rating = $this->guideRatingApiUseCase->deleteGuideRating($id);
            return ApiResponse::sendResponse(200,  __('app.api.rating-deleted-successfully'), []);
        } catch (\Exception $e) {
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $e->getMessage());
        }
    }

    public function show(Request $request)
    {
        $id = $request->guide_id;

        $validator = Validator::make(['guide_id' => $id], [
            'guide_id' => ['required', 'exists:users,id' ,new CheckIfTheIdIsGuideRule(),new CheckIfUserMadeRatingRule()],
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $errors);
        }
        try{
            $rating = $this->guideRatingApiUseCase->showGuideRating($id);
            return ApiResponse::sendResponse(200,  __('app.api.rating-retrieved-successfully'), $rating);
        } catch (\Exception $e) {
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $e->getMessage());
        }

    }
}

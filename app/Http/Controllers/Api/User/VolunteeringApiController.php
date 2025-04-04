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
use Illuminate\Support\Facades\Log;
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
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);

            return ApiResponse::sendResponse(Response::HTTP_BAD_REQUEST, __("validation.api.something-went-wrong"), $e->getMessage());
        }
    }

    public function activeVolunteerings()
    {
        try {
            $volunteering = $this->volunteeringApiUseCase->activeVolunteerings();
            return ApiResponse::sendResponse(200, 'Active Volunteering Retrieved Successfully', $volunteering);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);
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
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);
            return ApiResponse::sendResponse(Response::HTTP_BAD_REQUEST, __("validation.api.something-went-wrong"), $e->getMessage());
        }
    }

    public function dateVolunteering(DayRequest $request)
    {

        try {
            $volunteering = $this->volunteeringApiUseCase->dateVolunteerings($request->validated());
            return ApiResponse::sendResponse(200, ' Volunteering of specific date Retrieved Successfully', $volunteering);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);
            return ApiResponse::sendResponse(Response::HTTP_BAD_REQUEST, __("validation.api.something-went-wrong"), $e->getMessage());
        }
    }

    public function interest(Request $request,$slug)
    {
        $validator = Validator::make(['slug' => $slug], [
            'slug' =>  ['required', 'exists:volunteerings,slug', new CheckUserInterestRule('App\Models\Volunteering')],
        ]);


        if ($validator->fails()) {
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $validator->errors()->messages()['slug']);
        }
        try {
            $events = $this->volunteeringApiUseCase->interestVolunteering($validator->validated()['slug']);
            return ApiResponse::sendResponse(200, 'You Add Volunteering in Interest  Successfully', $events);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST, $e->getMessage());
        }
    }

    public function disinterest(Request $request,$slug)
    {
        $validator = Validator::make(['slug' => $slug], [
            'slug' => ['required', 'exists:volunteerings,slug', new CheckUserInterestExistsRule('App\Models\Volunteering')],
        ]);

        if ($validator->fails()) {
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $validator->errors()->messages()['slug']);
        }
        try {
            $events = $this->volunteeringApiUseCase->disinterestVolunteering($validator->validate()['slug']);
            return ApiResponse::sendResponse(200, 'You delete volunteering in Interest  Successfully', $events);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST, $e->getMessage());
        }
    }

    public function search(Request $request)
    {
        $query = $request->input('query');
        try {
            $places = $this->volunteeringApiUseCase->search($query);
            return ApiResponse::sendResponse(200, __('app.the-searched-volunteering-retrieved-successfully'), $places);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $e->getMessage());
        }
    }

    public function interestedList()
    {
        try {
            $id = Auth::guard('api')->user()->id;
            $volunteering = $this->volunteeringApiUseCase->interestedList($id);
            return ApiResponse::sendResponse(200, 'Volunteering Retrieved Successfully', $volunteering);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);
            return ApiResponse::sendResponse(Response::HTTP_BAD_REQUEST, __("validation.api.something-went-wrong"), $e->getMessage());
        }
    }
}

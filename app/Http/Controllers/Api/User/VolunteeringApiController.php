<?php

namespace App\Http\Controllers\Api\User;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\User\Event\DayRequest;
use App\Rules\CheckIfHasInjectionBasedTimeRule;
use App\Rules\CheckUserInterestExistsRule;
use App\Rules\CheckUserInterestRule;
use App\UseCases\Api\User\VolunteeringApiUseCase;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class VolunteeringApiController extends Controller
{
    public function __construct(protected VolunteeringApiUseCase $volunteeringApiUseCase)
    {
        $this->volunteeringApiUseCase = $volunteeringApiUseCase;
    }

    public function index()
    {
        try {
            $volunteering = $this->volunteeringApiUseCase->allVolunteerings();
            return ApiResponse::sendResponse(200, __('app.api.volunteering-retrieved-successfully'), $volunteering);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);
            return ApiResponse::sendResponse(Response::HTTP_BAD_REQUEST, __("validation.api.something-went-wrong"), $e->getMessage());
        }
    }

    public function activeVolunteerings()
    {
        try {
            $volunteering = $this->volunteeringApiUseCase->activeVolunteerings();
            return ApiResponse::sendResponse(200, __('app.api.active-volunteering-retrieved-successfully'), $volunteering);
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
        ], [
            'volunteering_slug.required' => __('validation.api.volunteering-id-is-required'),
            'volunteering_slug.exists' => __('validation.api.volunteering-id-does-not-exist'),
        ]);

        if ($validator->fails()) {
            return ApiResponse::sendResponse(Response::HTTP_BAD_REQUEST, __("validation.api.something-went-wrong"), $validator->errors());
        }

        $data = $validator->validated();
        try {
            $volunteering = $this->volunteeringApiUseCase->Volunteering($data['volunteering_slug']);
            return ApiResponse::sendResponse(200, __('app.api.volunteering-retrieved-successfully'), $volunteering);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);
            return ApiResponse::sendResponse(Response::HTTP_BAD_REQUEST, __("validation.api.something-went-wrong"), $e->getMessage());
        }
    }

    public function dateVolunteering(DayRequest $request)
    {
        try {
            $volunteering = $this->volunteeringApiUseCase->dateVolunteerings($request->validated());
            return ApiResponse::sendResponse(200, __('app.api.volunteering-of-specific-date-retrieved-successfully'), $volunteering);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);
            return ApiResponse::sendResponse(Response::HTTP_BAD_REQUEST, __("validation.api.something-went-wrong"), $e->getMessage());
        }
    }

    public function interest(Request $request, $slug)
    {
        $validator = Validator::make(['slug' => $slug], [
            'slug' =>  ['required', 'exists:volunteerings,slug', new CheckUserInterestRule('App\Models\Volunteering')],
        ], [
            'slug.required' => __('validation.api.volunteering-id-is-required'),
            'slug.exists' => __('validation.api.volunteering-id-does-not-exist'),
        ]);

        if ($validator->fails()) {
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $validator->errors()->messages()['slug']);
        }

        try {
            $events = $this->volunteeringApiUseCase->interestVolunteering($validator->validated()['slug']);
            return ApiResponse::sendResponse(200, __('app.api.you-add-this-volunteering-to-interest-successfully'), $events);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST, $e->getMessage());
        }
    }

    public function disinterest(Request $request, $slug)
    {
        $validator = Validator::make(['slug' => $slug], [
            'slug' => ['required', 'exists:volunteerings,slug', new CheckUserInterestExistsRule('App\Models\Volunteering')],
        ], [
            'slug.required' => __('validation.api.volunteering-id-is-required'),
            'slug.exists' => __('validation.api.volunteering-id-does-not-exist'),
        ]);

        if ($validator->fails()) {
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $validator->errors()->messages()['slug']);
        }

        try {
            $events = $this->volunteeringApiUseCase->disinterestVolunteering($validator->validate()['slug']);
            return ApiResponse::sendResponse(200, __('app.api.you-remove-this-volunteering-to-interest-successfully'), $events);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST, $e->getMessage());
        }
    }

    public function search(Request $request)
    {
        $query = $request->input('query');
        $validator = Validator::make(['query' => $query], [
            'query' => ['bail','nullable','string','max:255','regex:/^[\p{Arabic}a-zA-Z0-9\s\-\_\.@]+$/u',new CheckIfHasInjectionBasedTimeRule()],
        ]);
        if ($validator->fails()) {
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $validator->errors()->messages()['query']);
        }
        $validatedQuery = $validator->validated()['query'];
        try {
            $places = $this->volunteeringApiUseCase->search($validatedQuery);
            return ApiResponse::sendResponse(200, __('app.api.the-searched-volunteering-retrieved-successfully'), $places);
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
            return ApiResponse::sendResponse(200, __('app.api.the-interest-volunteering-retrieved-successfully'), $volunteering);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);
            return ApiResponse::sendResponse(Response::HTTP_BAD_REQUEST, __("validation.api.something-went-wrong"), $e->getMessage());
        }
    }
}

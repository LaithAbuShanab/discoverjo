<?php

namespace App\Http\Controllers\Api\User;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\User\Event\DayRequest;
use App\Rules\CheckUserInterestExistsRule;
use App\Rules\CheckUserInterestRule;
use App\UseCases\Api\User\EventApiUseCase;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class EventApiController extends Controller
{
    public function __construct(protected EventApiUseCase $eventApiUseCase)
    {
        $this->eventApiUseCase = $eventApiUseCase;
    }

    public function index()
    {
        try {
            $events = $this->eventApiUseCase->allEvents();
            return ApiResponse::sendResponse(200, __('app.api.events-retrieved-successfully'), $events);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $e->getMessage());
        }
    }

    public function activeEvents()
    {
        try {
            $events = $this->eventApiUseCase->activeEvents();
            return ApiResponse::sendResponse(200, __('app.api.active-events-retrieved-successfully'), $events);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $e->getMessage());
        }
    }

    public function event(Request $request)
    {
        $slug = $request->event_slug;
        $validator = Validator::make(['event_slug' => $slug], [
            'event_slug' => 'required|exists:events,slug',
        ], [
            'event_slug.required' => __('validation.api.event-id-is-required'),
            'event_slug.exists' => __('validation.api.event-id-does-not-exists'),
        ]);

        $data = $validator->validated();
        if ($validator->fails()) {
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $validator->errors());
        }
        try {
            $events = $this->eventApiUseCase->event($data['event_slug']);
            return ApiResponse::sendResponse(200, __('app.api.event-retrieved-successfully'), $events);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);

            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST, $e->getMessage());
        }
    }

    public function dateEvents(DayRequest $request)
    {
        try {
            $events = $this->eventApiUseCase->dateEvents($request->validated());
            return ApiResponse::sendResponse(200, __('app.api.events-of-specific-date-retrieved-successfully'), $events);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);

            return ApiResponse::sendResponse(Response::HTTP_BAD_REQUEST, __("validation.api.something-went-wrong"), $e->getMessage());
        }
    }

    public function interest(Request $request, $slug)
    {
        $validator = Validator::make(['slug' => $slug], [
            'slug' => ['bail', 'required', 'exists:events,slug', new CheckUserInterestRule('App\Models\Event')],
        ], [
            'slug.required' => __('validation.api.event-id-is-required'),
            'slug.exists' => __('validation.api.event-id-does-not-exists'),
        ]);


        if ($validator->fails()) {
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $validator->errors()->messages()['slug']);
        }

        try {
            $events = $this->eventApiUseCase->interestEvent($validator->validated()['slug']);
            return ApiResponse::sendResponse(200,  __('app.api.you-add-this-event-to-interest-successfully'), $events);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);

            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST, $e->getMessage());
        }
    }

    public function disinterest(Request $request, $slug)
    {
        $validator = Validator::make(['slug' => $slug], [
            'slug' => ['required', 'exists:events,slug', new CheckUserInterestExistsRule('App\Models\Event')],
        ], [
            'slug.required' => __('validation.api.event-id-is-required'),
            'slug.exists' => __('validation.api.event-id-does-not-exists'),
        ]);


        if ($validator->fails()) {
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $validator->errors()->messages()['slug']);
        }

        try {
            $event = $this->eventApiUseCase->disinterestEvent($validator->validated()['slug']);
            return ApiResponse::sendResponse(200, __('app.api.you-remove-this-event-from-interested-list'), $event);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST, $e->getMessage());
        }
    }

    public function search(Request $request)
    {
        $query = $request->input('query');
        $validator = Validator::make(['query' => $query], [
            'query' => ['nullable','string','max:255','regex:/^[\p{Arabic}a-zA-Z0-9\s\-\_\.@]+$/u'],
        ]);
        $validatedQuery = $validator->validated()['query'];
        try {
            $events = $this->eventApiUseCase->search($validatedQuery);
            return ApiResponse::sendResponse(200, __('app.api.the-searched-event-retrieved-successfully'), $events);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $e->getMessage());
        }
    }

    public function interestList()
    {
        try {
            $id = Auth::guard('api')->user()->id;
            $events = $this->eventApiUseCase->interestList($id);
            return ApiResponse::sendResponse(200, __('app.api.the-interest-event-retrieved-successfully'), $events);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $e->getMessage());
        }
    }
}

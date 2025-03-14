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
use App\UseCases\Api\User\EventApiUseCase;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class EventApiController extends Controller
{
    protected $eventApiUseCase;

    public function __construct(EventApiUseCase $eventApiUseCase)
    {

        $this->eventApiUseCase = $eventApiUseCase;
    }

    public function index()
    {
        try {
            $events = $this->eventApiUseCase->allEvents();
            return ApiResponse::sendResponse(200, __('app.event.api.events-retrieved-successfully'), $events);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);

            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $e->getMessage());
        }
    }

    public function activeEvents()
    {
        try {
            $events = $this->eventApiUseCase->activeEvents();
            return ApiResponse::sendResponse(200, __('app.event.api.active-events-retrieved-successfully'), $events);
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

        $data =$validator->validated();
        if ($validator->fails()) {
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $validator->errors());
        }
        try {
            $events = $this->eventApiUseCase->event($data['event_slug']);
            return ApiResponse::sendResponse(200, __('app.event.api.event-retrieved-successfully'), $events);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);

            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST, $e->getMessage());
        }
    }

    public function dateEvents(DayRequest $request)
    {

        try {
            $events = $this->eventApiUseCase->dateEvents($request->validated());
            return ApiResponse::sendResponse(200, __('app.event.api.events-of-specific-date-retrieved-successfully'), $events);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);

            return ApiResponse::sendResponse(Response::HTTP_BAD_REQUEST, __("validation.api.something-went-wrong"), $e->getMessage());
        }
    }

    public function interest(Request $request,$slug)
    {
        $validator = Validator::make(['slug' => $slug], [
            'slug' => ['required', 'exists:events,slug', new CheckUserInterestRule('App\Models\Event')],
        ], [
            'slug.required' => __('validation.api.event-id-is-required'),
            'slug.exists' => __('validation.api.event-id-does-not-exists'),
        ]);


        if ($validator->fails()) {
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $validator->errors()->messages()['slug']);
        }
        try {
            $events = $this->eventApiUseCase->interestEvent($validator->validated()['slug']);
            return ApiResponse::sendResponse(200,  __('app.event.api.you-add-this-event-to-interest-successfully'), $events);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);

            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST, $e->getMessage());
        }
    }

    public function disinterest(Request $request,$slug)
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
            $events = $this->eventApiUseCase->disinterestEvent($validator->validated()['slug']);
            return ApiResponse::sendResponse(200, __('app.event.api.you-remove-this-event-from-interested-list'), $events);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);

            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST, $e->getMessage());
        }
    }

    public function favorite(Request $request)
    {

        $id = $request->event_id;

        $validator = Validator::make(
            ['event_id' => $id],
            [
                'event_id' => [
                    'required',
                    'exists:events,id',
                    new CheckIfExistsInFavoratblesRule('App\Models\Event'),
                ],
            ],
            [
                'event_id.required' => __('validation.api.event-id-is-required'),
                'event_id.exists' => __('validation.api.event-id-does-not-exists'),
            ]
        );

        if ($validator->fails()) {
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $validator->errors()->messages()['event_id']);
        }
        try {
            $events = $this->eventApiUseCase->favorite($id);
            return ApiResponse::sendResponse(200, __('app.event.api.you-added-event-in-favorite-successfully'), $events);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);

            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST, $e->getMessage());
        }
    }

    public function deleteFavorite(Request $request)
    {
        $id = $request->event_id;
        $validator = Validator::make(
            ['event_id' => $id],
            [
                'event_id' => ['required', 'exists:events,id', new CheckIfNotExistsInFavoratblesRule('App\Models\Event')]
            ],
            [
                'event_id.required' => __('validation.api.event-id-is-required'),
                'event_id.exists' => __('validation.api.event-id-does-not-exists'),
            ]
        );


        if ($validator->fails()) {
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $validator->errors()->messages()['event_id']);
        }
        try {
            $events = $this->eventApiUseCase->deleteFavorite($id);
            return ApiResponse::sendResponse(200,  __('app.event.api.you-remove-event-from-favorite-successfully'), $events);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);

            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST, $e->getMessage());
        }
    }

    public function addReview(Request $request)
    {
        $validator = Validator::make(
            [
                'event_id' => $request->event_id,
                'rating' => $request->rating,
                'comment' => $request->comment
            ],
            [
                'event_id' => ['required', 'exists:events,id', new CheckIfExistsInReviewsRule('App\Models\Event'), new CheckIfPastEventOrVolunteering('App\Models\Event')],
                'rating' => ['required', 'numeric'],
                'comment' => ['nullable', 'string']
            ],
            [
                'event_id.required' => __('validation.api.event-id-is-required'),
                'event_id.exists' => __('validation.api.event-id-does-not-exists'),
                'rating.required' => __('validation.api.rating-is-required'),
                'comment.string' => __('validation.api.comment-should-be-string'),

            ]
        );

        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $errors);
        }

        try {
            $trip = $this->eventApiUseCase->addReview($validator->validated());
            return ApiResponse::sendResponse(200, __('app.event.api.you-added-review-for-this-event-successfully'), $trip);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);

            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST, $e->getMessage());
        }
    }

    public function updateReview(Request $request)
    {

        $validator = Validator::make(
            [
                'event_id' => $request->event_id,
                'rating' => $request->rating,
                'comment' => $request->comment
            ],
            [
                'event_id' => ['required', 'exists:events,id', new CheckIfExistsInToUpdateReviewsRule('App\Models\Event')],
                'rating' => ['required', 'numeric'],
                'comment' => ['nullable', 'string']
            ],
            [
                'event_id.required' => __('validation.api.event-id-is-required'),
                'event_id.exists' => __('validation.api.event-id-does-not-exists'),
                'rating.required' => __('validation.api.rating-is-required'),
                'comment.string' => __('validation.api.comment-should-be-string'),

            ]
        );


        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $errors);
        }
        try {
            $event = $this->eventApiUseCase->updateReview($validator->validated());
            return ApiResponse::sendResponse(200, __('app.event.api.you-update-your-review-successfully'), $event);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);

            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST, $e->getMessage());
        }
    }

    public function deleteReview(Request $request)
    {
        $validator = Validator::make(
            [
                'event_id' => $request->event_id,
            ],
            [
                'event_id' => ['required', 'exists:events,id', new CheckIfExistsInToUpdateReviewsRule('App\Models\Event')],
            ],
            [
                'event_id.required' => __('validation.api.event-id-is-required'),
                'event_id.exists' => __('validation.api.event-id-does-not-exists'),
                'rating.required' => __('validation.api.rating-is-required'),
                'comment.string' => __('validation.api.comment-should-be-string'),

            ]
        );

        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $errors);
        }
        try {
            $event = $this->eventApiUseCase->deleteReview($validator->validated());
            return ApiResponse::sendResponse(200, __('app.event.api.you-deleted-your-review-successfully'), $event);
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
                'review_id' => ['required', 'integer', 'exists:reviewables,id']
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
            $this->eventApiUseCase->reviewsLike($request);
            return ApiResponse::sendResponse(200, __('app.event.api.the-likable-status-change-successfully'), []);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);

            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $e->getMessage());
        }
    }

    public function search(Request $request)
    {
        $query = $request->input('query');
        try {
            $events = $this->eventApiUseCase->search($query);
            return ApiResponse::sendResponse(200, __('app.event.api.the-searched-event-retrieved-successfully'), $events);
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
            return ApiResponse::sendResponse(200, __('app.event.api.the-interest-event-retrieved-successfully'), $events);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $e->getMessage());
        }
    }
}

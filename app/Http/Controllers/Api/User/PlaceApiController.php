<?php

namespace App\Http\Controllers\Api\User;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\User\Place\FilterPlaceRequest;
use App\Rules\ActivePlaceRule;
use App\Rules\CheckIfExistsInVistedPlaceTableRule;
use App\Rules\CheckIfNotExistsInVistedPlaceTableRule;
use App\UseCases\Api\User\PlaceApiUseCase;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

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

            return ApiResponse::sendResponse(200, __('app.api.you-put-this-place-in-visited-place-list'), $createVisitedPlace);
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
            return ApiResponse::sendResponse(200, __('app.api.remove-place-form-visited-places-list-successfully'), $deleteVisitedPlace);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST, $e->getMessage());
        }
    }

    public function search(Request $request)
    {
        $query = $request->input('query');
        $validator = Validator::make(['query' => $query], [
            'query' => 'nullable|string|max:255'
        ]);
        $validatedQuery = $validator->validated()['query'];
        try {
            $places = $this->placeApiUseCase->search($validatedQuery);
            return ApiResponse::sendResponse(200, __('app.api.the-searched-place-retrieved-successfully'), $places);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $e->getMessage());
        }
    }

    public function filter(FilterPlaceRequest $request)
    {
        try {
            $places = $this->placeApiUseCase->filter($request->validated());
            return ApiResponse::sendResponse(200,  __('app.api.the-searched-place-retrieved-successfully'), $places);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $e->getMessage());
        }
    }

    public function allSearch(Request $request)
    {
        $query = $request->input('query');
        $validator = Validator::make(['query' => $query], [
            'query' => 'nullable|string|max:255'
        ]);
        $validatedQuery = $validator->validated()['query'];
        try {
            $places = $this->placeApiUseCase->allSearch($validatedQuery);
            return ApiResponse::sendResponse(200, __('app.api.the-searched-place-retrieved-successfully'), $places);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $e->getMessage());
        }
    }
}

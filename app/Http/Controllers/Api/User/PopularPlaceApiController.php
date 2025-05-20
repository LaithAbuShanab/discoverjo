<?php

namespace App\Http\Controllers\Api\User;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\UseCases\Api\User\PopularPlaceApiUseCase;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class PopularPlaceApiController extends Controller
{
    public function __construct(protected PopularPlaceApiUseCase $popularPlaceApiUseCase)
    {
        $this->popularPlaceApiUseCase = $popularPlaceApiUseCase;
    }

    /**
     * Display a listing of the resource.
     */
    public function popularPlaces()
    {
        $lat = request()->lat;
        $lng = request()->lng;
        $validator = Validator::make(['lat' => $lat, 'lng' => $lng], [
            'lat'   => [
                'bail',
                'nullable',
                'regex:/^-?\d{1,3}(\.\d{1,6})?$/',   // up to 6 decimal places
                'numeric',
                'between:-90,90',
            ],
            'lng'   => [
                'bail',
                'nullable',
                'regex:/^-?\d{1,3}(\.\d{1,6})?$/',  // up to 6 decimal places
                'numeric',
                'between:-180,180',
            ]
        ]);
        try {
            $popularPlaces = $this->popularPlaceApiUseCase->popularPlaces($validator->validate());

            return ApiResponse::sendResponse(200, __('app.api.popular-places-retrieved-successfully'), $popularPlaces);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);
            return ApiResponse::sendResponse(Response::HTTP_BAD_REQUEST, __("validation.api.something-went-wrong"), $e->getMessage());
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
            $places = $this->popularPlaceApiUseCase->search($validatedQuery);
            return ApiResponse::sendResponse(200, __('app.api.the-searched-places-retrieved-successfully'), $places);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $e->getMessage());
        }
    }
}

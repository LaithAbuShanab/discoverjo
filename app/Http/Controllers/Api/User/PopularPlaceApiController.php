<?php

namespace App\Http\Controllers\Api\User;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Rules\CheckIfHasInjectionBasedTimeRule;
use App\Rules\CheckLatLngRule;
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

        try {
            $popularPlaces = $this->popularPlaceApiUseCase->popularPlaces();

            return ApiResponse::sendResponse(200, __('app.api.popular-places-retrieved-successfully'), $popularPlaces);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);
            return ApiResponse::sendResponse(Response::HTTP_BAD_REQUEST, __("validation.api.something-went-wrong"), $e->getMessage());
        }
    }

    public function search(Request $request)
    {
        $query = $request->input('query');
        $lat = request()->lat;
        $lng = request()->lng;
        $validator = Validator::make(
            ['query' => $query, 'lat' => $lat, 'lng' => $lng],
            [
                'query' => ['bail','nullable','string','max:255','regex:/^[\p{Arabic}a-zA-Z0-9\s\-\_\.@]+$/u',
//                    new CheckIfHasInjectionBasedTimeRule()
                ],
                'lat'   => [
                    'bail',
                    'nullable',
//                    'regex:/^-?\d{1,3}(\.\d{1,6})?$/',   // up to 6 decimal places
//                    'numeric',
                    'between:-90,90',
                    new CheckLatLngRule()
                ],
                'lng'   => [
                    'bail',
                    'nullable',
//                    'regex:/^-?\d{1,3}(\.\d{1,6})?$/',  // up to 6 decimal places
//                    'numeric',
                    'between:-180,180',
                    new CheckLatLngRule()
                ],
            ]
        );
        if ($validator->fails()) {
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $validator->errors()->messages()['query']);
        }
        $data = $validator->validated();
        try {
            $places = $this->popularPlaceApiUseCase->search($data);
            return ApiResponse::sendResponse(200, __('app.api.the-searched-places-retrieved-successfully'), $places);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $e->getMessage());
        }
    }
}

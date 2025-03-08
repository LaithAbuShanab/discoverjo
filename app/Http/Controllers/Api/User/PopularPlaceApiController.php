<?php

namespace App\Http\Controllers\Api\User;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\UseCases\Api\User\PopularPlaceApiUseCase;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PopularPlaceApiController extends Controller
{
    protected $popularPlaceApiUseCase;

    public function __construct(PopularPlaceApiUseCase $popularPlaceApiUseCase) {

        $this->popularPlaceApiUseCase = $popularPlaceApiUseCase;

    }
    /**
     * Display a listing of the resource.
     */

    public function popularPlaces()
    {
        try{
            $popularPlaces = $this->popularPlaceApiUseCase->popularPlaces();

            return ApiResponse::sendResponse(200, __('app.api.popular-places-retrieved-successfully'), $popularPlaces);
        } catch (\Exception $e) {
            return ApiResponse::sendResponse(Response::HTTP_BAD_REQUEST, __("validation.api.something-went-wrong"), $e->getMessage());
        }
    }


    public function search(Request $request)
    {
        $query = $request->input('query');
        try {
            $places = $this->popularPlaceApiUseCase->search($query);
            return ApiResponse::sendResponse(200, __('app.api.the-searched-places-retrieved-successfully'), $places);
        } catch (\Exception $e) {
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $e->getMessage());
        }
    }
}

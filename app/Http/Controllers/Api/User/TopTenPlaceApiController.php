<?php

namespace App\Http\Controllers\Api\User;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\UseCases\Api\User\TopTenPlaceApiUseCase;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

class TopTenPlaceApiController extends Controller
{
    protected $topTenPlaceApiUseCase;

    public function __construct(TopTenPlaceApiUseCase $topTenPlaceApiUseCase) {

        $this->topTenPlaceApiUseCase = $topTenPlaceApiUseCase;

    }
    /**
     * Display a listing of the resource.
     */

    public function topTenPlaces()
    {
        try{
            $topTenPlaces = $this->topTenPlaceApiUseCase->topTenPlaces();

            return ApiResponse::sendResponse(200, __('app.api.top-ten-places-retrieved-successfully'), $topTenPlaces);
        } catch (\Exception $e) {
            return ApiResponse::sendResponse(Response::HTTP_BAD_REQUEST, __("validation.api.something-went-wrong"), $e->getMessage());
        }
    }

    public function search(Request $request)
    {
        $query = $request->input('query');
        try{
            $topTenPlaces = $this->topTenPlaceApiUseCase->search($query);

            return ApiResponse::sendResponse(200, __('app.api.searched-top-ten-places-retrieved-successfully'), $topTenPlaces);
        } catch (\Exception $e) {
            return ApiResponse::sendResponse(Response::HTTP_BAD_REQUEST, __("validation.api.something-went-wrong"), $e->getMessage());
        }
    }
}

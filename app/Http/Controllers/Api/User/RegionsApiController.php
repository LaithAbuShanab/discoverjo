<?php

namespace App\Http\Controllers\Api\User;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\UseCases\Api\User\RegionsApiUseCase;
use Illuminate\Http\Response;
use Illuminate\Http\Request;

class RegionsApiController extends Controller
{
    protected $RegionsApiUseCase;

    public function __construct(RegionsApiUseCase $RegionsApiUseCase)
    {

        $this->RegionsApiUseCase = $RegionsApiUseCase;
    }

    public function index()
    {
        try {
            $regions = $this->RegionsApiUseCase->allRegions();
            return ApiResponse::sendResponse(200, __('app.regions-retrieved-successfully'), $regions);
        } catch (\Exception $e) {
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $e->getMessage());
        }
    }
}

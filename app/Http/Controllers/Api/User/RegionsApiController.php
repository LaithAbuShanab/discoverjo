<?php

namespace App\Http\Controllers\Api\User;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\UseCases\Api\User\RegionsApiUseCase;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class RegionsApiController extends Controller
{
    public function __construct(protected RegionsApiUseCase $RegionsApiUseCase)
    {
        $this->RegionsApiUseCase = $RegionsApiUseCase;
    }

    public function index()
    {
        try {
            $regions = $this->RegionsApiUseCase->allRegions();
            return ApiResponse::sendResponse(200, __('app.api.regions-retrieved-successfully'), $regions);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $e->getMessage());
        }
    }
}

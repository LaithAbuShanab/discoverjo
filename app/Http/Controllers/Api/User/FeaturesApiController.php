<?php

namespace App\Http\Controllers\Api\User;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\UseCases\Api\User\FeaturesApiUseCase;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class FeaturesApiController extends Controller
{
    public function __construct(protected FeaturesApiUseCase $featuresApiUseCase)
    {
        $this->featuresApiUseCase = $featuresApiUseCase;
    }

    public function index()
    {
        try {
            $features = $this->featuresApiUseCase->allFeatures();
            return ApiResponse::sendResponse(200, __('app.api.features-retrieved-successfully'), $features);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $e->getMessage());
        }
    }
}

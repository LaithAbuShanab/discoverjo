<?php

namespace App\Http\Controllers\Api\User;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\UseCases\Api\User\FeaturesApiUseCase;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class FeaturesApiController extends Controller
{
    protected $featuresApiUseCase;

    public function __construct(FeaturesApiUseCase $featuresApiUseCase)
    {

        $this->featuresApiUseCase = $featuresApiUseCase;
    }

    public function index()
    {
        try {
            $features = $this->featuresApiUseCase->allFeatures();
            return ApiResponse::sendResponse(200, __('app.features-retrieved-successfully'), $features);
        } catch (\Exception $e) {
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $e->getMessage());
        }
    }
}

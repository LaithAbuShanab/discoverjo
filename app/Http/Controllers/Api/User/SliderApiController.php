<?php

namespace App\Http\Controllers\Api\User;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\UseCases\Api\User\SliderApiUseCase;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class SliderApiController extends Controller
{
    public function __construct(protected SliderApiUseCase $sliderUseCase)
    {
        $this->sliderUseCase = $sliderUseCase;
    }

    /**
     * Display a listing of the resource.
     */
    public function onboardings()
    {
        try {
            $categories = $this->sliderUseCase->allOnboardings();
            return ApiResponse::sendResponse(200, __('app.api.onboardings-retrieved-successfully'), $categories);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $e->getMessage());
        }
    }
}

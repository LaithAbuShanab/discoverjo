<?php

namespace App\Http\Controllers\Api\User;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\UseCases\Api\User\SliderApiUseCase;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;


class SliderApiController extends Controller
{
    protected $sliderApiUseCase;

    public function __construct(SliderApiUseCase $sliderUseCase) {

        $this->sliderApiUseCase = $sliderUseCase;

    }
    /**
     * Display a listing of the resource.
     */
    public function onboardings()
    {
        try{
            $categories = $this->sliderApiUseCase->allOnboardings();
            return ApiResponse::sendResponse(200, __('app.api.onboardings-retrieved-successfully'), $categories);
        } catch (\Exception $e) {
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $e->getMessage());
        }
    }




}

<?php

namespace App\Http\Controllers\Api\User;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\User\Service\BookingDateRequest;
use App\Http\Requests\Api\User\Service\ServiceReservationRequest;
use App\Rules\CheckIfProviderActiveRule;
use App\Rules\CheckIfServiceActiveRuel;
use App\UseCases\Api\User\ServiceApiUseCase;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;


class ServiceApiController extends Controller
{
    public function __construct(protected ServiceApiUseCase $serviceApiUseCase)
    {
        $this->serviceApiUseCase = $serviceApiUseCase;
    }

    public function index()
    {
        try {
            $services = $this->serviceApiUseCase->allServices();
            return ApiResponse::sendResponse(200, __('app.api.services-retrieved-successfully'), $services);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $e->getMessage());
        }
    }


}

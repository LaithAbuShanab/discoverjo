<?php

namespace App\Http\Controllers\Api\User;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\User\Property\CheckAvailableMonthRequest;
use App\Http\Requests\Api\User\Property\CheckAvailableRequest;
use App\UseCases\Api\User\PropertyReservationApiUseCase;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Request;

class PropertyReservationApiController extends Controller
{
    public function __construct(protected PropertyReservationApiUseCase $propertyReservationApiUseCase)
    {
        $this->propertyReservationApiUseCase = $propertyReservationApiUseCase;
    }

    public function checkAvailable(CheckAvailableRequest $request)
    {
        $data = $request->validated();
        try {
            $services = $this->propertyReservationApiUseCase->checkAvailable($data);
            return ApiResponse::sendResponse(200, __('app.api.sessions-retrieved-successfully'), $services);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $e->getMessage());
        }
    }

    public function checkAvailableMonth(CheckAvailableMonthRequest $request)
    {
        $data = $request->validated();
        try {
            $services = $this->propertyReservationApiUseCase->checkAvailableMonth($data);
            return ApiResponse::sendResponse(200, __('app.api.sessions-retrieved-successfully'), $services);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $e->getMessage());
        }
    }

}

<?php

namespace App\Http\Controllers\Api\User;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\User\Service\BookingDateRequest;
use App\Http\Requests\Api\User\Service\ServiceReservationRequest;
use App\Http\Requests\Api\User\Service\UpdateReservationRequest;
use App\Rules\CheckIfProviderActiveRule;
use App\Rules\CheckIfReservationBelongToProvider;
use App\Rules\CheckIfReservationIdBelongToUser;
use App\Rules\CheckIfServiceActiveRuel;
use App\Rules\CheckIfServiceBelongToProvider;
use App\Rules\CheckIfServiceReservationInThePastByIdRule;
use App\UseCases\Api\User\ReservationApiUseCase;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ReservationApiController extends Controller
{
    public function __construct(protected ReservationApiUseCase $reservationApiUseCase)
    {
        $this->reservationApiUseCase = $reservationApiUseCase;
    }

    public function reservationDate(BookingDateRequest $request)
    {
        $data = $request->validated();
        try {
            $services = $this->reservationApiUseCase->reservationDate($data);
            return ApiResponse::sendResponse(200, __('app.api.sessions-retrieved-successfully'), $services);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $e->getMessage());
        }
    }

    public function serviceReservation(ServiceReservationRequest $request)
    {
        $data = $request->validated();
        try {
            $services = $this->reservationApiUseCase->serviceReservation($data);
            return ApiResponse::sendResponse(200, __('app.api.reservation-created-successfully'), $services);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $e->getMessage());
        }
    }

    public function UserServiceReservations(Request $request, $service_slug)
    {
        $validator = Validator::make(['service_slug' => $service_slug], [
            'service_slug' => ['required', 'exists:services,slug', new CheckIfServiceActiveRuel(), new CheckIfProviderActiveRule()],
        ], [
            'service_slug.required' => __('validation.api.service-id-required'),
            'service_slug.exists' => __('validation.api.service-id-does-not-exists'),
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $errors);
        }
        try {
            $services = $this->reservationApiUseCase->UserServiceReservations($validator->validated());
            return ApiResponse::sendResponse(200, __('app.api.reservations-retrieved-successfully'), $services);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $e->getMessage());
        }
    }

    public function allReservations()
    {
        try {
            $services = $this->reservationApiUseCase->allReservations();
            return ApiResponse::sendResponse(200, __('app.api.reservations-retrieved-successfully'), $services);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $e->getMessage());
        }
    }

    public function deleteReservation($id)
    {
        $validator = Validator::make(['id' => $id], [
            'id' => ['bail', 'required', 'exists:service_reservations,id', new CheckIfReservationIdBelongToUser()],
        ], [
            'id.required' => __('validation.api.reservation-id-required'),
            'id.exists' => __('validation.api.reservation-id-does-not-exists'),
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $errors);
        }

        try {
            $services = $this->reservationApiUseCase->deleteReservation($validator->validated()['id']);
            return ApiResponse::sendResponse(200, __('app.api.reservation-deleted-successfully'), $services);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $e->getMessage());
        }
    }

    public function updateReservation(UpdateReservationRequest $request, $id)
    {
        $validator = Validator::make(['id' => $id], [
            'id' => ['bail', 'required', 'exists:service_reservations,id', new CheckIfReservationIdBelongToUser(), new CheckIfServiceReservationInThePastByIdRule()],
        ], [
            'id.required' => __('validation.api.reservation-id-required'),
            'id.exists' => __('validation.api.reservation-id-does-not-exists'),
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $errors);
        }

        $data = array_merge($request->validated(), ['id' => $id]);

        try {
            $services = $this->reservationApiUseCase->updateReservation($data);
            return ApiResponse::sendResponse(200, __('app.api.reservation-updated-successfully'), $services);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $e->getMessage());
        }
    }

    public function changeStatusReservation($id, $status)
    {
        $validator = Validator::make(['id' => $id, 'status' => $status], [
            'id' => ['bail', 'required', 'exists:service_reservations,id', new CheckIfReservationBelongToProvider()],
            'status' => ['bail', 'required', Rule::in(['confirmed', 'cancelled'])],
        ], [
            'id.required' => __('validation.api.reservation-id-required'),
            'id.exists' => __('validation.api.reservation-id-does-not-exists'),
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $errors);
        }
        $data = $validator->validated();

        try {
            $services = $this->reservationApiUseCase->changeStatusReservation($data);
            return ApiResponse::sendResponse(200, __('app.api.reservation-status-updated-successfully'), $services);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $e->getMessage());
        }
    }

    public function providerRequestReservations(Request $request, $service_slug)
    {
        $validator = Validator::make(['service_slug' => $service_slug], [
            'service_slug' => ['required', 'exists:services,slug', new CheckIfServiceBelongToProvider()],
        ], [
            'service_slug.required' => __('validation.api.service-id-required'),
            'service_slug.exists' => __('validation.api.service-id-does-not-exists'),
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $errors);
        }
        try {
            $services = $this->reservationApiUseCase->providerRequestReservations($validator->validated()['service_slug']);
            return ApiResponse::sendResponse(200, __('app.api.reservations-request-retrieved-successfully'), $services);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $e->getMessage());
        }
    }

    public function approvedRequestReservations(Request $request, $service_slug)
    {
        $validator = Validator::make(['service_slug' => $service_slug], [
            'service_slug' => ['required', 'exists:services,slug', new CheckIfServiceBelongToProvider()],
        ], [
            'service_slug.required' => __('validation.api.service-id-required'),
            'service_slug.exists' => __('validation.api.service-id-does-not-exists'),
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $errors);
        }
        try {
            $services = $this->reservationApiUseCase->approvedRequestReservations($validator->validated()['service_slug']);
            return ApiResponse::sendResponse(200, __('app.api.reservations-approved-successfully'), $services);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $e->getMessage());
        }
    }
}

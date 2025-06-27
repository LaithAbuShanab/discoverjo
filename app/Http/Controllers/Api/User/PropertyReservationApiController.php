<?php

namespace App\Http\Controllers\Api\User;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\User\Property\CheckAvailableMonthRequest;
use App\Http\Requests\Api\User\Property\CheckAvailableRequest;
use App\Http\Requests\Api\User\Property\CheckPriceRequest;
use App\Http\Requests\Api\User\Property\MakeReservationRequest;
use App\Rules\CheckIfPropertyBlongToHostRule;
use App\Rules\CheckIfPropertyReservaionBelongToUser;
use App\Rules\CheckIfPropertyReservationBlongToHostRule;
use App\UseCases\Api\User\PropertyReservationApiUseCase;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\Api\User\Property\UpdateReservationRequest;
use Illuminate\Validation\Rule;

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
            $property = $this->propertyReservationApiUseCase->checkAvailable($data);
            return ApiResponse::sendResponse(200, __('app.api.available-dates-retrieved-successfully'), $property);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $e->getMessage());
        }
    }

    public function checkAvailableMonth(CheckAvailableMonthRequest $request)
    {
        $data = $request->validated();
        try {
            $property = $this->propertyReservationApiUseCase->checkAvailableMonth($data);
            return ApiResponse::sendResponse(200, __('app.api.available-dates-retrieved-successfully'), $property);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $e->getMessage());
        }
    }

    public function checkPrice(CheckPriceRequest $request)
    {
        $data = $request->validated();
        try {
            $property = $this->propertyReservationApiUseCase->CheckPrice($data);
            return ApiResponse::sendResponse(200, __('app.api.price-retrieved-successfully'), $property);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $e->getMessage());
        }
    }

    public function makeReservation(MakeReservationRequest $request)
    {
        $data = $request->validated();
        try {
            $property = $this->propertyReservationApiUseCase->makeReservation($data);
            return ApiResponse::sendResponse(200, __('app.api.reservation-created-successfully'), $property);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $e->getMessage());
        }
    }

    public function updateReservation(UpdateReservationRequest $request, $id)
    {
        $validator = Validator::make(['id' => $id], [
            'id' => ['bail', 'required', 'exists:property_reservations,id', new CheckIfPropertyReservaionBelongToUser()],
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
            $property = $this->propertyReservationApiUseCase->updateReservation($data);
            return ApiResponse::sendResponse(200, __('app.api.reservation-updated-successfully'), $property);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $e->getMessage());
        }
    }

    public function deleteReservation($id)
    {
        $validator = Validator::make(['id' => $id], [
            'id' => ['bail', 'required', 'exists:property_reservations,id', new CheckIfPropertyReservaionBelongToUser()],
        ], [
            'id.required' => __('validation.api.reservation-id-required'),
            'id.exists' => __('validation.api.reservation-id-does-not-exists'),
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $errors);
        }

        try {
            $property = $this->propertyReservationApiUseCase->deleteReservation($validator->validated()['id']);
            return ApiResponse::sendResponse(200, __('app.api.reservation-deleted-successfully'), $property);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $e->getMessage());
        }
    }

    public function allPropertyReservations($property_slug)
    {
        $validator = Validator::make(['property_slug' => $property_slug], [
            'property_slug' => ['bail', 'required', 'exists:properties,slug'],
        ], [
            'property_slug.required' => __('validation.api.the-property-id-required'),
            'property_slug.exists' => __('validation.api.the-selected-property-id-does-not-exists'),
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $errors);
        }

        try {
            $property = $this->propertyReservationApiUseCase->allPropertyReservations($validator->validated()['property_slug']);
            return ApiResponse::sendResponse(200, __('app.api.all-property-reservations-retrieved-successfully'), $property);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $e->getMessage());
        }
    }

    public function allReservations()
    {
        try {
            $property = $this->propertyReservationApiUseCase->allReservations();
            return ApiResponse::sendResponse(200, __('app.api.all-reservation-retrieved-successfully'), $property);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $e->getMessage());
        }
    }

    public function changeStatusReservation($id, $status)
    {
        $validator = Validator::make(['id' => $id, 'status' => $status], [
            'id' => ['bail', 'required', 'exists:property_reservations,id', new CheckIfPropertyReservationBlongToHostRule()],
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
            $property = $this->propertyReservationApiUseCase->changeStatusReservation($data);
            return ApiResponse::sendResponse(200, __('app.api.reservation-status-updated-successfully'), $property);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $e->getMessage());
        }
    }

    public function RequestReservations(Request $request, $property_slug)
    {
        $validator = Validator::make(['property_slug' => $property_slug], [
            'property_slug' => ['required', 'exists:properties,slug', new CheckIfPropertyBlongToHostRule()],
        ], [
            'property_slug.required' => __('validation.api.property-id-required'),
            'property_slug.exists' => __('validation.api.property-id-does-not-exists'),
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $errors);
        }
        try {
            $property = $this->propertyReservationApiUseCase->RequestReservations($validator->validated()['property_slug']);
            return ApiResponse::sendResponse(200, __('app.api.reservations-request-retrieved-successfully'), $property);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $e->getMessage());
        }
    }

    public function approvedRequestReservations(Request $request, $property_slug)
    {
        $validator = Validator::make(['property_slug' => $property_slug], [
            'property_slug' => ['required', 'exists:properties,slug', new CheckIfPropertyBlongToHostRule()],
        ], [
            'property_slug.required' => __('validation.api.property-id-required'),
            'property_slug.exists' => __('validation.api.property-id-does-not-exists'),
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $errors);
        }
        try {
            $property = $this->propertyReservationApiUseCase->approvedRequestReservations($validator->validated()['property_slug']);
            return ApiResponse::sendResponse(200, __('app.api.reservations-approved-successfully'), $property);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $e->getMessage());
        }
    }
}

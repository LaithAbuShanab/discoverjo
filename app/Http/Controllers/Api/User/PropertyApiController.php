<?php

namespace App\Http\Controllers\Api\User;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Rules\CheckIfHostIsActiveRule;
use App\UseCases\Api\User\PropertyApiUseCase;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class PropertyApiController extends Controller
{
    public function __construct(protected PropertyApiUseCase $propertyApiUseCase)
    {
        $this->propertyApiUseCase = $propertyApiUseCase;
    }

    public function getAllChalets()
    {
        try {
            $chalets = $this->propertyApiUseCase->getAllChalets();
            return ApiResponse::sendResponse(200, __('app.api.chalets-retrieved-successfully'), $chalets);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $e->getMessage());
        }
    }

    public function singleProperty(Request $request, $property_slug)
    {
        $slug = $property_slug;

        $validator = Validator::make(['property_slug' => $slug], [
            'property_slug' => ['bail', 'required', 'exists:properties,slug', new CheckIfHostIsActiveRule()],
        ], [
            'property_slug.exists' => __('validation.api.the-selected-property-id-does-not-exists'),
            'property_slug.required' => __('validation.api.the-property-id-required'),
        ]);

        $data = $validator->validated();

        if ($validator->fails()) {
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST, $validator->errors()->messages()['property_slug']);
        }
        try {
            $allPlaces = $this->propertyApiUseCase->singleProperty($data['property_slug']);
            return ApiResponse::sendResponse(200,  __('app.api.property-retrieved-by-id-successfully'), $allPlaces);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $e->getMessage());
        }
    }
}

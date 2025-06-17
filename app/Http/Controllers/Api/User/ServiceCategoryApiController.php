<?php

namespace App\Http\Controllers\Api\User;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\User\Event\DayRequest;
use App\Http\Requests\Api\User\Service\SubcategoriesOfServiceCategoriesRequest;
use App\Rules\CheckIfHasInjectionBasedTimeRule;
use App\Rules\CheckIfProviderActiveRule;
use App\Rules\CheckIfServiceCategoryIsParentRule;
use App\UseCases\Api\User\ServiceCategoryApiUseCase;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ServiceCategoryApiController extends Controller
{
    public function __construct(protected ServiceCategoryApiUseCase $serviceCategoryApiUseCase)
    {
        $this->serviceCategoryApiUseCase = $serviceCategoryApiUseCase;
    }

    public function index()
    {
        try {
            $serviceCategories = $this->serviceCategoryApiUseCase->allServiceCategories();
            return ApiResponse::sendResponse(200, __('app.api.services-retrieved-successfully'), $serviceCategories);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $e->getMessage());
        }
    }

    public function categoryServices(Request $request)
    {
        $slug = $request->category_slug;

        $validator = Validator::make(['category_slug' => $slug], [
            'category_slug' => ['bail', 'required', 'exists:service_categories,slug', new CheckIfServiceCategoryIsParentRule()],
        ], [
            'category_slug.exists' => __('validation.api.the-selected-category-id-does-not-exists'),
            'category_slug.required' => __('validation.api.the-category-id-required'),
        ]);


        $data = $validator->validated();

        if ($validator->fails()) {
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST, $validator->errors()->messages()['category_slug']);
        }
        try {
            $allPlaces = $this->serviceCategoryApiUseCase->allServiceByCategory($data);
            return ApiResponse::sendResponse(200,  __('app.api.service-subcategories-retrieved-successfully'), $allPlaces);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $e->getMessage());
        }
    }

    public function subcategoriesOfCategories(SubcategoriesOfServiceCategoriesRequest $request)
    {
        $data = $request->validated();
        $data = array_map('trim', explode(',', $data['categories']));
        try {
            $categories = $this->serviceCategoryApiUseCase->allSubcategories($data);
            return ApiResponse::sendResponse(200, __('app.api.all-subcategories-retrieved-successfully'), $categories);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $e->getMessage());
        }
    }

    public function search(Request $request)
    {
        $query = $request->input('query');
        $validator = Validator::make(['query' => $query], [
            'query' => ['bail', 'nullable', 'string', 'max:255', 'regex:/^[\p{Arabic}a-zA-Z0-9\s\-\_\.@]+$/u', new CheckIfHasInjectionBasedTimeRule()],
        ]);
        if ($validator->fails()) {
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST, $validator->errors()->messages()['query']);
        }
        $validatedQuery = $validator->validated()['query'];
        try {
            $places = $this->serviceCategoryApiUseCase->search($validatedQuery);
            return ApiResponse::sendResponse(200, __('app.api.the-searched-categories-retrieved-successfully'), $places);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $e->getMessage());
        }
    }

    public function dateServices(DayRequest $request)
    {
        try {
            $trips = $this->serviceCategoryApiUseCase->dateServices($request->validated());
            return ApiResponse::sendResponse(200, __('app.api.services-of-specific-date-retrieved-successfully'), $trips);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);

            return ApiResponse::sendResponse(Response::HTTP_BAD_REQUEST, __("validation.api.something-went-wrong"), $e->getMessage());
        }
    }

    public function singleService(Request $request, $slug)
    {

        $validator = Validator::make(['service_slug' => $slug], [
            'service_slug' => ['required', 'exists:services,slug', new CheckIfProviderActiveRule()],
        ], [
            'service_slug.required' => __('validation.api.service-id-required'),
            'service_slug.exists' => __('validation.api.service-id-does-not-exists'),
        ]);
        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $errors);
        }
        try {
            $data = $validator->validated();
            $service = $this->serviceCategoryApiUseCase->singleService($data['service_slug']);
            return ApiResponse::sendResponse(200, __('app.api.services-retrieved-successfully'), $service);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST, $e->getMessage());
        }
    }
}

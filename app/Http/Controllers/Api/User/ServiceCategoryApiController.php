<?php

namespace App\Http\Controllers\Api\User;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Rules\CheckIfCategoryIsParentRule;
use App\Rules\CheckIfServiceCategoryIsParentRule;
use App\Rules\CheckLatLngRule;
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
            'category_slug' => ['bail','required', 'exists:service_categories,slug', new CheckIfServiceCategoryIsParentRule()],
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
            return ApiResponse::sendResponse(200,  __('app.api.places-subcategories-retrieved-successfully'), $allPlaces);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $e->getMessage());
        }
    }

    public function subcategoriesOfCategories(SubcategoriesOfCategoriesRequest $request)
    {
        $data = $request->validated();
        $data = array_map('trim', explode(',', $data['categories']));
        try {
            $categories = $this->categoryApiUseCase->allSubcategories($data);
            return ApiResponse::sendResponse(200, __('app.api.all-subcategories-retrieved-successfully'), $categories);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $e->getMessage());
        }
    }
}

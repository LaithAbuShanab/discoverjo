<?php

namespace App\Http\Controllers\Api\User;

use App\Helpers\ApiResponse;
use App\Http\Requests\Api\User\category\SubcategoriesOfCategoriesRequest;
use App\Http\Controllers\Controller;
use App\Rules\CheckIfCategoryIsParentRule;
use App\UseCases\Api\User\CategoryApiUseCase;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;


class CategoryApiController extends Controller
{

    public function __construct(protected CategoryApiUseCase $categoryApiUseCase)
    {
        $this->categoryApiUseCase = $categoryApiUseCase;
    }

    public function index()
    {
        try {
            $categories = $this->categoryApiUseCase->allCategories();
            return ApiResponse::sendResponse(200, __('app.api.categories-retrieved-successfully'), $categories);
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

    public function shuffleAllCategories()
    {
        try {
            $categories = $this->categoryApiUseCase->shuffleAllCategories();
            return ApiResponse::sendResponse(200, __('app.api.categories-retrieved-successfully'), $categories);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $e->getMessage());
        }
    }

    public function categoryPlaces(Request $request)
    {
        $slug = $request->category_slug;
        $validator = Validator::make(['category_slug' => $slug], [
            'category_slug' => ['required', 'exists:categories,slug', new CheckIfCategoryIsParentRule()],
        ], [
            'category_slug.exists' => __('validation.api.the-selected-category-id-does-not-exists'),
            'category_slug.required' => __('validation.api.the-category-id-required'),
        ]);

        if ($validator->fails()) {
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST, $validator->errors()->messages()['category_slug']);
        }
        try {
            $allPlaces = $this->categoryApiUseCase->allPlacesByCategory($validator->validated()['category_slug']);
            return ApiResponse::sendResponse(200,  __('app.api.places-subcategories-retrieved-successfully'), $allPlaces);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $e->getMessage());
        }
    }

    public function search(Request $request)
    {
        $query = $request->input('query');
        try {
            $places = $this->categoryApiUseCase->search($query);
            return ApiResponse::sendResponse(200, __('app.api.the-searched-categories-retrieved-successfully'), $places);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);

            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $e->getMessage());
        }
    }
}

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
        $request->merge([
            'lat' => $request->has('lat') ? floatval($request->lat) : null,
            'lng' => $request->has('lng') ? floatval($request->lng) : null,
        ]);
        $slug = $request->category_slug;
        $lat = $request->lat;
        $lng = $request->lng;

        $validator = Validator::make(['category_slug' => $slug, 'lat' => $lat, 'lng' => $lng], [
            'category_slug' => ['bail','required', 'exists:categories,slug', new CheckIfCategoryIsParentRule()],
            'lat'   => [
                'bail',
                'nullable',
//                'regex:/^-?\d{1,3}(\.\d{1,6})?$/',  // up to 6 decimal places
//                'numeric',
                'between:-90,90',
            ],
            'lng'   => [
                'bail',
                'nullable',
//                'regex:/^-?\d{1,3}(\.\d{1,6})?$/',  // up to 6 decimal places
//                'numeric',
                'between:-180,180',
            ],
        ], [
            'category_slug.exists' => __('validation.api.the-selected-category-id-does-not-exists'),
            'category_slug.required' => __('validation.api.the-category-id-required'),
        ]);


        $data = $validator->validated();

        if ($validator->fails()) {
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST, $validator->errors()->messages()['category_slug']);
        }
        try {
            $allPlaces = $this->categoryApiUseCase->allPlacesByCategory($data);
            return ApiResponse::sendResponse(200,  __('app.api.places-subcategories-retrieved-successfully'), $allPlaces);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $e->getMessage());
        }
    }

    public function search(Request $request)
    {
        $query = $request->input('query');
        $validator = Validator::make(['query' => $query], [
            'query' => 'nullable|string|max:255'
        ]);
        $validatedQuery = $validator->validated()['query'];
        try {
            $places = $this->categoryApiUseCase->search($validatedQuery);
            return ApiResponse::sendResponse(200, __('app.api.the-searched-categories-retrieved-successfully'), $places);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $e->getMessage());
        }
    }
}

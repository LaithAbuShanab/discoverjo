<?php

namespace App\Http\Controllers\Api\User;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Rules\CheckLatLngRule;
use App\UseCases\Api\User\SubCategoryApiUseCase;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;


class SubCategoryApiController extends Controller
{

    public function __construct(protected SubCategoryApiUseCase $subCategoryApiUseCase)
    {
        $this->subCategoryApiUseCase = $subCategoryApiUseCase;
    }

    public function singleSubCategory(Request $request)
    {
        $lat = request()->lat;
        $lng = request()->lng;
        $slug = $request->subcategory_slug;
        $validator = Validator::make(['subcategory_slug' => $slug, 'lat' => $lat, 'lng' => $lng], [
            'subcategory_slug' => [
                'bail',
                'required',
                'regex:/^[\p{Arabic}a-zA-Z0-9\s\-\_\.@]+$/u',
                'exists:categories,slug',
                function ($attribute, $value, $fail) {
                    if (Category::where('slug', $value)->whereNull('parent_id')->exists()) {
                        $fail(__('app.api.this-is-main-category'));
                    }
                }
            ],
            'lat'   => [
                'bail',
                'nullable',
//                'regex:/^-?\d{1,3}(\.\d{1,6})?$/',   // up to 6 decimal places
//                'numeric',
                'between:-90,90',
                new CheckLatLngRule()
            ],
            'lng'   => [
                'bail',
                'nullable',
//                'regex:/^-?\d{1,3}(\.\d{1,6})?$/',  // up to 6 decimal places
//                'numeric',
                'between:-180,180',
                new CheckLatLngRule()
            ],
            [
                'subcategory_slug.required' => __('validation.api.subcategory-is-required'),
                'subcategory_slug.exists' => __('validation.api.subcategory-does-not-exists'),
            ]
        ]);

        if ($validator->fails()) {
            return ApiResponse::sendResponse(Response::HTTP_BAD_REQUEST, __("validation.api.something-went-wrong"), $validator->errors());
        }
        try {
            $data = $validator->validated();
            $subCategory = $this->subCategoryApiUseCase->singleSubCategory($data);

            return ApiResponse::sendResponse(200, __('app.api.places-of-subcategories-retrieved-successfully'), $subCategory);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);
            return ApiResponse::sendResponse(Response::HTTP_BAD_REQUEST, __("validation.api.something-went-wrong"), $e->getMessage());
        }
    }
}

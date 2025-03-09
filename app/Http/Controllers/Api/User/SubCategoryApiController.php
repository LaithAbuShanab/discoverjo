<?php

namespace App\Http\Controllers\Api\User;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\UseCases\Api\User\SubCategoryApiUseCase;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;


class SubCategoryApiController extends Controller
{

    public function __construct(protected SubCategoryApiUseCase $subCategoryApiUseCase)
    {
        $this->subCategoryApiUseCase = $subCategoryApiUseCase;
    }

    public function singleSubCategory(Request $request)
    {
        $slug = $request->subcategory_slug;
        $validator = Validator::make(['subcategory_slug' => $slug], [
            'subcategory_slug' => [
                'required',
                'exists:categories,slug',
                function ($attribute, $value, $fail) {
                    if (Category::where('slug', $value)->whereNull('parent_id')->exists()) {
                        $fail(__('app.api.this-is-main-category'));
                    }
                }
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
            $subCategory = $this->subCategoryApiUseCase->singleSubCategory($data['subcategory_slug']);

            return ApiResponse::sendResponse(200, __('app.api.places-of-subcategories-retrieved-successfully'), $subCategory);
        } catch (\Exception $e) {
            return ApiResponse::sendResponse(Response::HTTP_BAD_REQUEST, __("validation.api.something-went-wrong"), $e->getMessage());
        }
    }
}

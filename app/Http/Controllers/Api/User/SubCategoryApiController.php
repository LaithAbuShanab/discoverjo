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

    protected $subCateogryApiUseCase;

    public function __construct(SubCategoryApiUseCase $subCateogryApiUseCase) {

        $this->subCateogryApiUseCase = $subCateogryApiUseCase;

    }
    public function singleSubCategory(Request $request)
    {
        $id = $request->subcategory_id;
        $validator = Validator::make(['subcategory_id' => $id], [
            'subcategory_id' => [
                'required',
                'exists:categories,id',
                function ($attribute, $value, $fail) {
                    if (Category::where('id', $value)->whereNull('parent_id')->exists()) {
                        $fail(__('app.api.this-is-main-category'));
                    }
                }
            ],[
                'subcategory_id.required'=>__('validation.api.subcategory-is-required'),
                'subcategory_id.exists'=>__('validation.api.subcategory-does-not-exists'),
            ]
        ]);

        if ($validator->fails()) {
            return ApiResponse::sendResponse(Response::HTTP_BAD_REQUEST, __("validation.api.something-went-wrong"), $validator->errors());
        }
        try{
            $subCategory = $this->subCateogryApiUseCase->singleSubCategory($id);

            return ApiResponse::sendResponse(200, __('app.api.places-of-subcategories-retrieved-successfully'), $subCategory);
        } catch (\Exception $e) {
            return ApiResponse::sendResponse(Response::HTTP_BAD_REQUEST, __("validation.api.something-went-wrong"), $e->getMessage());
        }
    }
}

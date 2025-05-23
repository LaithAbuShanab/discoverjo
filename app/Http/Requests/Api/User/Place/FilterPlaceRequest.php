<?php

namespace App\Http\Requests\Api\User\Place;

use App\Helpers\ApiResponse;
use App\Models\Category;
use App\Models\Feature;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Response;

class FilterPlaceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'categories' => ['bail','nullable','regex:/^[\p{Arabic}a-zA-Z0-9\s\-_\,]+$/u', function ($attribute, $value, $fail) {
                $values = explode(',', $value);
                if (!is_array($values) || empty($values)) {
                    return $fail(__('validation.api.the-categories-be-string-separated-by-comma'));
                }

                foreach ($values as $slug) {
                    $category = Category::findBySlug($slug);
                    if (!$category) {
                        return $fail(__('validation.api.the-category-does-not-exists'));
                    }
                    if ($category->parent_id !== null) {
                        return $fail(__('validation.api.the-selected-category-must-be-main'));
                    }
                }
            }],

            'subcategories' => ['bail','nullable','regex:/^[\p{Arabic}a-zA-Z0-9\s\-_\,]+$/u',function ($attribute, $value, $fail) {
                $values = explode(',', $value);
                if (!is_array($values)) {
                    return $fail(__('validation.api.the-subcategories-must-be-string-separated-by-comma'));
                }

                foreach ($values as $slug) {
                    $category = Category::findBySlug($slug);
                    if (!$category) {
                        return $fail(__('validation.api.the-subcategory-does-not-exist'));
                    }
                    if ($category->parent_id === null) {
                        return $fail(__('validation.api.the-selected-subcategory-must-not-be-main'));
                    }
                }
            }],

            'region' => 'bail|nullable|string|exists:regions,slug',
            'min_cost' =>'bail|nullable|integer|between:1,4',
            'max_cost' =>'bail|nullable|integer|between:1,4|gte:min_cost',
            'features' => ['bail','nullable','regex:/^[\p{Arabic}a-zA-Z0-9\s\-_\,]+$/u', function ($attribute, $value, $fail) {
                $values = explode(',', $value);
                if (!is_array($values)) {
                    return $fail(__('validation.api.the-features-must-be-string-separated-by-comma'));
                }

                foreach ($values as $slug) {
                    $feature = Feature::findBySlug($slug);
                    if (!$feature) {
                        return $fail(__('validation.api.the-feature-does-not-exist'));
                    }
                }
            }],
            'min_rate' => 'bail|nullable|integer|between:1,5',
            'max_rate' => 'bail|nullable|integer|between:1,5|gte:min_rate',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        $errors = $validator->errors()->all();
        throw new HttpResponseException(
            ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST, $errors)
        );
    }
}

<?php

namespace App\Http\Requests;

use App\Helpers\ApiResponse;
use App\Models\Category;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Response;


class PlacesOfCurrentLocationRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */

    // In your FormRequest class


    public function rules(): array
    {
        return [
            'lng' => ['bail','numeric', 'nullable', 'regex:/^-?\d{1,3}(\.\d{1,6})?$/', 'between:-180,180',],
            'lat' => ['bail', 'numeric','nullable', 'regex:/^-?\d{1,3}(\.\d{1,6})?$/', 'between:-90,90'],
            'area' => ['nullable','numeric'],
            'categories' => ['nullable', function ($attribute, $value, $fail) {
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
            'subcategories' => ['nullable', function ($attribute, $value, $fail) {
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
        ];
    }

    public function messages()
    {
        return [
            'lng.required' => __('validation.api.lng-required'),
            'lat.required' => __('validation.api.lat-required'),
            'categories_id.*' => __('validation.api.categories-id-invalid'),
            'subcategories_id.*' => __('validation.api.subcategories-id-invalid'),
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        $errors = $validator->errors()->all();

        throw new HttpResponseException(
            ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $errors)
        );
    }
}

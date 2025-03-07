<?php

namespace App\Http\Requests;

use App\Helpers\ApiResponse;
use App\Models\Category;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;


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
            'lng' => ['required'],
            'lat' => ['required'],
            'area' => ['nullable'],
            'categories_id' => ['nullable', function ($attribute, $value, $fail) {
                $value = json_decode($value);
                if (is_array($value)) {
                    foreach ($value as $id) {
                        $category = Category::find($id);
                        if (!$category) $fail(__('validation.api.the-category-does-not-exists'));
                        if ( $category?->parent_id !=null) $fail(__('validation.api.the-selected-category-does-not-main-category'));
                    }
                }else{
                    $fail(__('validation.api.the-category-should-be-array'));
                }
            }],
            'subcategories_id' => ['nullable', function ($attribute, $value, $fail) {
                $value = json_decode($value);
                if (is_array($value)) {
                    foreach ($value as $id) {
                        $category = Category::find($id);
                        if (!$category) $fail('The subcategory does not exists');
                        if ( $category?->parent_id ==null) $fail(__('validation.api.the-selected-subcategory-it-is-main-category'));
                    }
                }else{
                    $fail(__('validation.api.the-subcategories-should-be-array'));
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

<?php

namespace App\Http\Requests\Api\User\Place;

use App\Helpers\ApiResponse;
use App\Models\Category;
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'categories_id' => ['nullable', function ($attribute, $value, $fail) {
                $value = json_decode($value);
                if (is_array($value)) {
                    foreach ($value as $id) {
                        $category = Category::find($id);
                        if (!$category) $fail('The category does not exists');
                        if ( $category?->parent_id !=null) $fail('The selected category does not main category');
                    }
                }else{
                    $fail('The categories should be array');
                }
            }],
            'subcategories_id' => ['nullable', function ($attribute, $value, $fail) {
                $value = json_decode($value);
                if (is_array($value)) {
                    foreach ($value as $id) {
                        $category = Category::find($id);
                        if (!$category) $fail('The subcategory does not exists');
                        if ( $category?->parent_id ==null) $fail('The selected subcategory does not main cateogy');
                    }
                }else{
                    $fail('The subcategories should be array');
                }
            }],
            'region_id'=>'nullable',
            'min_cost' => 'nullable|integer|between:1,4',
            'max_cost' => 'nullable|integer|between:1,4|gte:min_cost',
            'features_id'=>'nullable',
            'features_id*.'=>'exists:features,id',
            'min_rate'=>'nullable|integer|between:1,5',
            'max_rate'=>'nullable|integer|between:1,4|gte:min_rate',
        ];
    }

    public function messages()
    {
        return [
            // Categories ID validation messages
            'categories_id.nullable' => __('validation.api.categories-id-nullable'),
            'categories_id.array' => __('validation.api.categories-id-array'),
            'categories_id.*.exists' => __('validation.api.category-exists'),
            'categories_id.*.parent_id' => __('validation.api.category-main'),

            // Subcategories ID validation messages
            'subcategories_id.nullable' => __('validation.api.subcategories-id-nullable'),
            'subcategories_id.array' => __('validation.api.subcategories-id-array'),
            'subcategories_id.*.exists' => __('validation.api.subcategory-exists'),
            'subcategories_id.*.parent_id' => __('validation.api.subcategory-main'),

            // Region ID validation message
            'region_id.nullable' => __('validation.api.region-id-nullable'),

            // Min and Max Cost validation messages
            'min_cost.nullable' => __('validation.api.min-cost-nullable'),
            'max_cost.nullable' => __('validation.api.max-cost-nullable'),

            // Features ID validation messages
            'features_id.nullable' => __('validation.api.features-id-nullable'),
            'features_id.*.exists' => __('validation.api.feature-exists'),

            // Min and Max Rate validation messages
            'min_rate.nullable' => __('validation.api.min-rate-nullable'),
            'max_rate.nullable' => __('validation.api.max-rate-nullable'),
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

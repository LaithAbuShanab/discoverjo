<?php

namespace App\Http\Requests\Api\User\category;

use App\Helpers\ApiResponse;
use App\Models\Category;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Response;

class SubcategoriesOfCategoriesRequest extends FormRequest
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
            'categories' => ['required', function ($attribute, $value, $fail) {
                $values = explode(',', $value);
                if (is_array($values)) {
                    foreach ($values as $slug) {
                        $slug = trim($slug);
                        $category = Category::findBySlug($slug);
                        if (!$category) $fail(__('validation.api.the-category-does-not-exists'));
                        if ($category?->parent_id != null) $fail(__('validation.api.the-selected-category-does-not-main-category'));
                    }
                } else {
                    $fail(__('validation.api.the-categories-should-be-array'));
                }
            }],
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        $errors = $validator->errors()->all();
        throw new HttpResponseException(ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST, $errors));
    }
}

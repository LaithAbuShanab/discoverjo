<?php

namespace App\Http\Requests\Api\User\Suggestion;

use App\Helpers\ApiResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Response;

class StoreSuggestionPlaceApiRequest extends FormRequest
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
            'place_name' => 'required',
            'address' => 'required',
            "images" => ['nullable'],
            'images.*' => ['image', 'mimes:jpeg,png,jpg,gif,svg,webp', 'max:10000'],
        ];
    }

    public function messages()
    {
        return [
            'place_name.required' => __('validation.api.place_name-required'),
            'address.required' => __('validation.api.address-required'),
            'images.*.image' => __('validation.api.images-image'),
            'images.*.mimes' => __('validation.api.images-mimes'),
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

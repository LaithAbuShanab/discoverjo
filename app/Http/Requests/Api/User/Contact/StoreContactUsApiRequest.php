<?php

namespace App\Http\Requests\Api\User\Contact;

use App\Helpers\ApiResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Response;

class StoreContactUsApiRequest extends FormRequest
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
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'subject' => 'nullable|string|max:255',
            'message' => 'required|string',
            "images" => ['nullable'],
            'images.*' => ['image', 'mimes:jpeg,png,jpg,gif,svg,webp,bmp,tiff,ico,svgz|max:10000'],
        ];
    }

    public function messages()
    {
        return [
            'name.required' => __('validation.api.name-is-required'),
            'email.required' => __('validation.api.email-is-required'),
            'email.email' => __('validation.api.email-invalid-format'),
            'subject.nullable' => __('validation.api.subject-nullable'),
            'message.required' => __('validation.api.message-is-required'),
            'images.array' => __('validation.api.images-optional'),
            'images.*.image' => __('validation.api.images-must-be-an-image'),
            'images.*.mimes' => __('validation.api.images-invalid-format'),
        ];
    }

    public function attributes()
    {
        return [
            'name' => __('validation.attributes.name'),
            'email' => __('validation.attributes.email'),
            'subject' => __('validation.attributes.subject'),
            'message' => __('validation.attributes.message'),
            'images' => __('validation.attributes.images'),
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

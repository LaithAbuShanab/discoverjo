<?php

namespace App\Http\Requests\Web\Admin\Notification;

use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class StoreNotificationRequest extends FormRequest
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
            'title_en' => ['required', 'string', 'min:3'],
            'title_ar' => ['required', 'string', 'min:3'],
            'body_ar' => ['required', 'string', 'min:3'],
            'body_en' => ['required', 'string', 'min:3'],

        ];
    }


    public function messages(): array
    {
        return [
            'name_en.required' => __('validation.msg.english-name-required'),
            'name_en.min' => __('validation.msg.english-name-min-characters'),
            'name_ar.required' => __('validation.msg.arabic-name-required'),
            'name_ar.min' => __('validation.msg.arabic-name-min-characters'),
            'priority.required' => __('validation.msg.priority-required'),
            'image.required' => __('validation.msg.image-required'),
            'image.image' => __('validation.msg.image-invalid'),
            'image.mimes' => __('validation.msg.image-mime', ['mime_types' => 'jpeg, png, jpg, gif, svg, webp, bmp, tiff, ico, svgz']),
        ];
    }



    public function attributes()
    {
        return [
            'name_en' => __('validation.attributes.name-en'),
            'name_ar' => __('validation.attributes.name-ar'),
            'priority' => __('validation.attributes.priority'),
            'image' => __('validation.attributes.image'),
        ];
    }


    protected function failedValidation(Validator $validator)
    {
        $errors = $validator->errors()->all();
        foreach ($errors as $error) {
            Toastr::error($error, 'Error');
        }
        throw new HttpResponseException(
            redirect()->back()->withInput()->withErrors($validator)
        );
    }
}

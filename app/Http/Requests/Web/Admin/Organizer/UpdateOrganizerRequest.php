<?php

namespace App\Http\Requests\Web\Admin\Organizer;

use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class UpdateOrganizerRequest extends FormRequest
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
        $organizerId = request()->id;
        return [
            'name_en' => ['required', 'string' ,'min:3', Rule::unique('organizers', 'name->en')->ignore($organizerId)],
            'name_ar' => ['required', 'string' ,'min:3', Rule::unique('organizers', 'name->ar')->ignore($organizerId)],
            'image' => ['nullable','image', 'mimes:jpeg,png,jpg,gif,svg,webp,bmp,tiff,ico,svgz'],
        ];
    }

    public function messages(): array
    {
        return [
            'name_en.required' => __('validation.msg.english-name-required'),
            'name_en.min' => __('validation.msg.english-name-min-characters'),
            'name_ar.required' => __('validation.msg.arabic-name-required'),
            'name_ar.min' => __('validation.msg.arabic-name-min-characters'),
            'image.image'=>__('validation.msg.image'),
            'image.mimes'=>__('validation.msg.mimes'),

        ];
    }

    public function attributes()
    {
        return [
            'name_en' => __('validation.attributes.name-en'),
            'name_ar' => __('validation.attributes.name-ar'),
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

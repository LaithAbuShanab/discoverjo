<?php

namespace App\Http\Requests\Web\Admin\Feature;

use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class UpdateFeatureRequest extends FormRequest
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
        $featureId = request()->id;
        return [
            'name_en' => ['required', 'min:3', Rule::unique('features', 'name->en')->ignore($featureId)],
            'name_ar' => ['required', 'min:3', Rule::unique('features', 'name->ar')->ignore($featureId)],
            'image_active' => ['nullable', 'image'],
            'image_inactive' => ['nullable', 'image'],
        ];
    }

    public function messages(): array
    {
        return [
            'name_en.required' => 'English name is required.',
            'name_en.min' => 'English name must be at least :min characters.',
            'name_ar.required' => 'Arabic name is required.',
            'name_ar.min' => 'Arabic name must be at least :min characters.',
            'image.max' => 'The image size must be 1024.',
        ];
    }

    public function attributes()
    {
        return [
            'name_en' => 'English Name',
            'name_ar' => 'Arabic Name',
            'image' => 'Image'
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

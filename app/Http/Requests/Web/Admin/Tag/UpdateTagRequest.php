<?php

namespace App\Http\Requests\Web\Admin\Tag;

use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class UpdateTagRequest extends FormRequest
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
        $tagId = request()->id;
        return [
            'name_en' => ['required', 'min:3', Rule::unique('tags', 'name->en')->ignore($tagId)],
            'name_ar' => ['required', 'min:3', Rule::unique('tags', 'name->ar')->ignore($tagId)],
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
            'image.required' => 'Image is required.',
        ];
    }

    public function attributes()
    {
        return [
            'name_en' => 'English Name',
            'name_ar' => 'Arabic Name',
            'image' => 'Image',
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

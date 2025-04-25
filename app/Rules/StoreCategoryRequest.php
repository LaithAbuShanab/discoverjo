<?php

namespace App\Rules;

use App\Rules\UniquePriorityWithinParent;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class StoreCategoryRequest extends FormRequest
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
        $category = $this->route('category'); // For update operation, or null for create
        $categoryId=$category?->id;
        $parentId = $this->input('parent_id');
        return [
            'name_en' => ['required', 'string', 'min:3', Rule::unique('categories', 'name->en')],
            'name_ar' => ['required', 'string', 'min:3', Rule::unique('categories', 'name->ar')],
            'priority' => ['required',new UniquePriorityWithinParent($parentId, $categoryId)],
            'image' => [
                'nullable',
                'image',
                'mimes:jpeg,png,jpg,gif,svg,webp,bmp,tiff,ico,svgz',
                function ($attribute, $value, $fail) use ($parentId) {
                    if (!$parentId && !$value) {
                        $fail(__('The :attribute field is required when parent_id is null.'));
                    }
                }
            ],
            'image_active' => [
                'nullable',
                'image',
                'mimes:jpeg,png,jpg,gif,svg,webp,bmp,tiff,ico,svgz',
                function ($attribute, $value, $fail) use ($parentId) {
                    if ($parentId && !$value) {
                        $fail(__('The :attribute field is required when parent_id is not null.'));
                    }
                }
            ],
            'image_inactive' => [
                'nullable',
                'image',
                'mimes:jpeg,png,jpg,gif,svg,webp,bmp,tiff,ico,svgz',
                function ($attribute, $value, $fail) use ($parentId) {
                    if ($parentId && !$value) {
                        $fail(__('The :attribute field is required when parent_id is not null.'));
                    }
                }
            ],
            'parent_id'=>['nullable','exists:categories,id'],


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

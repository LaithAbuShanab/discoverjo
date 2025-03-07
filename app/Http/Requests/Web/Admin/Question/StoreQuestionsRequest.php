<?php

namespace App\Http\Requests\Web\Admin\Question;

use Illuminate\Foundation\Http\FormRequest;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreQuestionsRequest extends FormRequest
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
            'question_en' => 'required',
            'question_ar' => 'required',
            'is_first_question' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'question_en.required' => __('validation.msg.question-name-en-required'),
            'question_ar.required' => __('validation.msg.question-name-ar-required'),
            'is_first_question.required' => __('validation.msg.is-first-question-required'),
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        $errors = $validator->errors()->all();
        foreach ($errors as $error) {
            Toastr::error($error, __('Error'));
        }
        throw new HttpResponseException(
            redirect()->back()->withInput()->withErrors($validator)
        );
    }
}
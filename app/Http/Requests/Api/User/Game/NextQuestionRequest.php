<?php

namespace App\Http\Requests\Api\User\Game;

use App\Helpers\ApiResponse;
use App\Rules\CheckIfCanMakeTripRule;
use App\Rules\CheckTagExistsRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;


class NextQuestionRequest extends FormRequest
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
            'question_id' => ['required', 'exists:questions,id'],
            'answer' => ['required', Rule::in('yes','no','i_dont_know')],

        ];
    }

    public function messages()
    {
        return [
            // Question ID
            'question_id.required' => __('validation.api.question-id-is-required'),
            'question_id.exists' => __('validation.api.question-id-not-exists'),

            // Answer
            'answer.required' => __('validation.api.answer-is-required'),
            'answer.in' => __('validation.api.answer-invalid-value'),
        ];
    }


    protected function failedValidation(Validator $validator)
    {
        $errors = $validator->errors()->all();
        throw new HttpResponseException(ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST, $errors));
    }
}

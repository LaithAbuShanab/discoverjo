<?php

namespace App\Http\Requests\Api\User\Post;

use App\Helpers\ApiResponse;
use App\Rules\CheckIfUserCanReplyOnCommentRule;
use App\Rules\IfUserCanMakeCommentInPostRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Response;

class CreateReplyRequest extends FormRequest
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
            'comment_id'=>['required','exists:comments,id',new CheckIfUserCanReplyOnCommentRule()],
            'content'=>['required','string'],
        ];
    }

    public function messages()
    {
        return [
            'comment_id.required' => __('validation.api.comment-id-required'),
            'comment_id.exists' => __('validation.api.comment-id-exists'),
            'content.required' => __('validation.api.content-required'),
            'content.string' => __('validation.api.content-string'),
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

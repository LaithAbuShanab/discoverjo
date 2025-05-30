<?php

namespace App\Http\Requests\Api\User\Post;

use App\Helpers\ApiResponse;
use App\Rules\CheckIfCommentBelongToUser;
use App\Rules\IfUserCanMakeCommentInPostRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Response;

class UpdateCommentRequest extends FormRequest
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
            'comment_id'=>['required','exists:comments,id',new CheckIfCommentBelongToUser()],
            'content'=>['required','string'],
        ];
    }

    public function messages()
    {
        return [
            'post_id.required' => __('validation.api.post-id-required'),
            'post_id.exists' => __('validation.api.post-id-exists'),
            'post_id.if_user_can_make_comment_in_post' => __('validation.api.post-id-can-make-comment'),
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

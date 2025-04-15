<?php

namespace App\Http\Requests\Api\User\Post;

use App\Helpers\ApiResponse;
use App\Rules\CheckIfCommentHasNullParentIdRule;
use App\Rules\CheckIfCommentOwnerActiveRule;
use App\Rules\CheckIfPostCreateorActiveRule;
use App\Rules\IfUserCanMakeCommentInPostRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Response;

class CreateCommentRequest extends FormRequest
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
     * @return array
     */
    public function rules(): array
    {
        return [
            'post_id' => [
                'bail',
                'required',
                'exists:posts,id',
                new IfUserCanMakeCommentInPostRule(),
                new CheckIfPostCreateorActiveRule()
            ],
            'content' => ['required', 'string'],
            'parent_id' => [
                'bail',
                'nullable',
                'exists:comments,id',
                new CheckIfCommentHasNullParentIdRule(),
                new CheckIfCommentOwnerActiveRule()
            ],
        ];
    }

    /**
     * Custom messages for validator errors.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'post_id.required' => __('validation.api.post-id-required'),
            'post_id.exists' => __('validation.api.post-id-exists'),
            'content.required' => __('validation.api.content-required'),
            'content.string' => __('validation.api.content-string'),
            'parent_id.exists' => __('validation.api.parent-id-exists'),
        ];
    }

    /**
     * Handle a failed validation attempt.
     *
     * @param Validator $validator
     */
    protected function failedValidation(Validator $validator)
    {
        $errors = $validator->errors()->all();
        throw new HttpResponseException(
            ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST, $errors)
        );
    }
}

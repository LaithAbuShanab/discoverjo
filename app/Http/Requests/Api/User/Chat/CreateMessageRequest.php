<?php

namespace App\Http\Requests\Api\User\Chat;

use App\Rules\GroupChatIndexRule;
use Illuminate\Foundation\Http\FormRequest;

class CreateMessageRequest extends FormRequest
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
            'conversation_id' => ['required', 'exists:conversations,id', new GroupChatIndexRule($this->conversation_id)],
            'message_txt' => 'nullable|string',
            'file' => 'nullable|file|mimes:jpg,jpeg,png,gif,mp3,wav,mp4,mov,avi|max:10000',
        ];
    }
    public function messages()
    {
        return [
            // Conversation ID
            'conversation_id.required' => __('validation.api.conversation-id-is-required'),
            'conversation_id.exists' => __('validation.api.conversation-id-must-exist'),
            'conversation_id.group_chat' => __('validation.api.conversation-id-invalid'),

            // Message Text
            'message_txt.required_if' => __('validation.api.message-txt-is-required-if-text'),
            'message_txt.string' => __('validation.api.message-txt-must-be-string'),

            // File
            'file.required_if' => __('validation.api.file-is-required-if-media'),
            'file.file' => __('validation.api.file-must-be-valid'),
            'file.mimes' => __('validation.api.file-invalid-type'),
            'file.max' => __('validation.api.file-max-size', ['max' => 10000]),
        ];
    }
}

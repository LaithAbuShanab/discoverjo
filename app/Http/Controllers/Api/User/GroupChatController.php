<?php

namespace App\Http\Controllers\Api\User;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\User\Chat\CreateMessageRequest;
use App\Rules\GroupChatIndexRule;
use App\UseCases\Api\User\GroupChatUseCase;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class GroupChatController extends Controller
{
    protected $groupChatUseCase;

    public function __construct(GroupChatUseCase $groupChatUseCase)
    {
        $this->groupChatUseCase = $groupChatUseCase;
    }

    public function messages(Request $request)
    {
        $validator = Validator::make(['conversation_id' => $request->conversation_id], [
            'conversation_id' => ['required', 'exists:conversations,id', new GroupChatIndexRule($request->conversation_id)],
        ],[
            'conversation_id.required'=>__('validation.api.conversation-id-is-required'),
            'conversation_id.exists'=>__('validation.api.conversation-id-does-not-exists'),
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $errors);
        }

        try {
            $data = $this->groupChatUseCase->getGroupMessages($request->conversation_id);
            return ApiResponse::sendResponse(200, __('app.group-chat.group-chat-retrieved-successfully'), $data);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $e->getMessage());
        }
    }

    public function members(Request $request)
    {
        $validator = Validator::make(['conversation_id' => $request->conversation_id], [
            'conversation_id' => ['required', 'exists:conversations,id', new GroupChatIndexRule($request->conversation_id)],
        ],[
            'conversation_id.required'=>__('validation.api.conversation-id-is-required'),
            'conversation_id.exists'=>__('validation.api.conversation-id-does-not-exists'),
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $errors);
        }

        try {
            $data = $this->groupChatUseCase->getGroupMembers($request->conversation_id);
            return ApiResponse::sendResponse(200, __('app.group-chat.group-chat-retrieved-successfully'), $data);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $e->getMessage());
        }
    }
    public function store(CreateMessageRequest $request)
    {
        try {
            $data = $this->groupChatUseCase->sendMessages($request);
            return ApiResponse::sendResponse(200, __('app.group-chat.message-sent-successfully'), $data);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $e->getMessage());
        }
    }
}

<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\UseCases\Api\User\SingleChatUseCase;
use App\Helpers\ApiResponse;
use App\Http\Requests\Api\User\Chat\CreateMessageRequest;
use App\Rules\CheckIfUserActiveRule;
use App\Rules\IsConversationMemberRule;
use App\Rules\PreventSelfChatRule;
use App\Rules\SingleChatRule;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class SingleChatController extends Controller
{
    public function __construct(protected SingleChatUseCase $singleChatUseCase) {}

    public function createSingleChat($slug)
    {
        $validator = Validator::make(['user_slug' => $slug], [
            'user_slug' => [
                'required',
                'exists:users,slug',
                new CheckIfUserActiveRule(),
                new PreventSelfChatRule(),
                new SingleChatRule(),
            ],
        ], [
            'user_slug.required' => __('validation.api.user_slug_required'),
            'user_slug.exists' => __('validation.api.user_slug_exists'),
        ]);

        $validatedData = $validator->validated();
        try {
            $data = $this->singleChatUseCase->createSingleChat($validatedData['user_slug']);
            return ApiResponse::sendResponse(200, __('app.api.single-chat-created-successfully'), $data);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $e->getMessage());
        }
    }

    public function listConversations()
    {
        try {
            $data = $this->singleChatUseCase->listConversations();
            return ApiResponse::sendResponse(200, __('app.api.list-of-conversations-retrieved-successfully'), $data);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $e->getMessage());
        }
    }

    public function singleConversation($conversation_id)
    {
        $validator = Validator::make(['conversation_id' => $conversation_id], [
            'conversation_id' => [
                'bail',
                'required',
                'exists:conversations,id',
                new IsConversationMemberRule(),
            ],
        ], [
            'conversation_id.required' => __('validation.api.conversation_id_required'),
            'conversation_id.exists' => __('validation.api.conversation_id_exists'),
        ]);

        $validatedData = $validator->validated();
        try {
            $data = $this->singleChatUseCase->getConversation($validatedData['conversation_id']);
            return ApiResponse::sendResponse(200, __('app.api.conversation-retrieved-successfully'), $data);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $e->getMessage());
        }
    }

    public function store(CreateMessageRequest $request)
    {
        try {
            $data = $this->singleChatUseCase->sendMessages($request);
            return ApiResponse::sendResponse(200, __('app.api.group-chat-message-sent-successfully'), $data);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $e->getMessage());
        }
    }
}

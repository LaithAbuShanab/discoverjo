<?php

namespace App\Http\Controllers\Api\User;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\User\Post\CreateReplyRequest;
use App\Rules\CheckIfReplyBelongToUser;
use App\Rules\CheckIfUserCanDeleteReply;
use App\UseCases\Api\User\ReplyApiUseCase;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ReplyApiController extends Controller
{
    protected $replyApiUseCase;

    public function __construct(ReplyApiUseCase $replyUseCase) {

        $this->replyApiUseCase = $replyUseCase;

    }
    /**
     * Display a listing of the resource.
     */
    public function replyStore(CreateReplyRequest $request)
    {
        try{
            $reply = $this->replyApiUseCase->createReply($request->validated());
            return ApiResponse::sendResponse(200, __('app.api.reply-created-successfully'), []);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);
            return ApiResponse::sendResponse(Response::HTTP_BAD_REQUEST, __("validation.api.something-went-wrong"), $e->getMessage());
        }
    }

    public function replyUpdate(Request $request,$reply_id)
    {
        $content = $request->input('content');
        $validator = Validator::make(
            [
                'reply_id' => $reply_id,
                'content' => $content
            ],
            [
                'reply_id' => ['required', 'exists:replies,id', new CheckIfReplyBelongToUser()],
                'content' => ['required','string']
            ],
            [
                'reply_id.required'=>__('validation.api.reply-id-is-required'),
                'reply_id.exists'=>__('validation.api.reply-id-does-not-exists'),
                'content.required'=>__('validation.api.content-is-required'),

            ]
        );

        if ($validator->fails()) {
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $validator->errors()->messages());
        }
        try{
            $replies = $this->replyApiUseCase->updateReply($validator->validated());
            return ApiResponse::sendResponse(200,  __('app.api.reply-updated-successfully'), []);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);
            return ApiResponse::sendResponse(Response::HTTP_BAD_REQUEST, __("validation.api.something-went-wrong"), $e->getMessage());
        }
    }

    public function replyDelete(Request $request,$reply_id){
        $id =$reply_id;

        $validator = Validator::make(
            [
                'reply_id' => $id,
            ],
            [
                'reply_id' => ['required', 'exists:replies,id', new CheckIfUserCanDeleteReply()],
            ],
            [
                'reply_id.required'=>__('app.api.reply-id-is-required'),
                'reply_id.exists'=>__('app.api.reply-id-does-not-exists'),
            ]
        );
        if ($validator->fails()) {
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $validator->errors()->messages());
        }
        try{
            $replies = $this->replyApiUseCase->deleteReply($id);
            return ApiResponse::sendResponse(200, __('app.api.reply-deleted-successfully'), []);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);
            return ApiResponse::sendResponse(Response::HTTP_BAD_REQUEST, __("validation.api.something-went-wrong"), $e->getMessage());
        }
    }

    public function likeDislike(Request $request,$status,$reply_id)
    {
        $validator = Validator::make(
            [
                'status' => $status,
                'reply_id' => $reply_id,
            ], [
                'status' => ['required', Rule::in(['like', 'dislike'])],
                'reply_id' => ['required', 'integer', 'exists:replies,id'],
            ],[
                'status.required'=>__('validation.api.status-is-required'),
                'reply_id.required'=>__('validation.api.reply-id-is-required'),
                'reply_id.exists'=>__('validation.api.reply-id-does-not-exists'),
            ]
        );

        if ($validator->fails()) {
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $validator->errors()->messages());
        }

        try {
            $this->replyApiUseCase->replyLike($request);
            return ApiResponse::sendResponse(200, __('app.event.api.the-likable-status-change-successfully'), []);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $e->getMessage());
        }
    }
}

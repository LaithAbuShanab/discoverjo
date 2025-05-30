<?php

namespace App\Http\Controllers\Api\User;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\User\Post\CreateCommentRequest;
use App\Rules\CheckIfCommentBelongToUser;
use App\Rules\CheckIfCommentOwnerActiveRule;
use App\Rules\CheckIfUserCanDeleteComment;
use App\UseCases\Api\User\CommentApiUseCase;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class CommentApiController extends Controller
{
    public function __construct(protected CommentApiUseCase $commentApiUseCase)
    {

        $this->commentApiUseCase = $commentApiUseCase;
    }
    /**
     * Display a listing of the resource.
     */
    public function commentStore(CreateCommentRequest $request)
    {
        try {
           $comment =  $this->commentApiUseCase->createComment($request->validated());
            return ApiResponse::sendResponse(200,  __('app.api.comment-created-successfully'), $comment);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);
            return ApiResponse::sendResponse(Response::HTTP_BAD_REQUEST, __("validation.api.something-went-wrong"), $e->getMessage());
        }
    }

    public function commentUpdate(Request $request, $comment_id)
    {
        $id = $comment_id;
        $content = $request->input('content');
        $validator = Validator::make(
            [
                'comment_id' => $id,
                'content' => $content,
            ],
            [
                'comment_id' => ['bail', 'required', 'exists:comments,id', new CheckIfCommentBelongToUser()],
                'content' => ['required', 'string'],
            ],
            [
                'comment_id.exists' => __('validation.api.the-selected-comment-id-does-not-exists'),
                'comment_id.required' => __('validation.api.the-comment-id-required'),
                'content.required' => __('validation.api.the-content-required'),
                'content.string' => __('validation.api.comment-should-be-string'),
            ]
        );

        if ($validator->fails()) {
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $validator->errors()->messages());
        }
        try {
            $this->commentApiUseCase->updateComment($validator->validated());
            return ApiResponse::sendResponse(200,  __('app.api.comment-updated-successfully'), []);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);

            return ApiResponse::sendResponse(Response::HTTP_BAD_REQUEST, __("validation.api.something-went-wrong"), $e->getMessage());
        }
    }

    public function commentDelete(Request $request, $comment_id)
    {

        $validator = Validator::make(
            [
                'comment_id' => $comment_id,
            ],
            [
                'comment_id' => ['bail', 'required', 'exists:comments,id', new CheckIfUserCanDeleteComment()],
            ],
            [
                'comment_id.exists' => __('validation.api.the-selected-comment-id-does-not-exists'),
                'comment_id.required' => __('validation.api.the-comment-id-required'),
            ]
        );
        if ($validator->fails()) {
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $validator->errors()->messages());
        }
        try {
            $comments = $this->commentApiUseCase->deleteComment($validator->validated()['comment_id']);
            return ApiResponse::sendResponse(200, __('app.api.comment-deleted-successfully'), []);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);

            return ApiResponse::sendResponse(Response::HTTP_BAD_REQUEST, __("validation.api.something-went-wrong"), $e->getMessage());
        }
    }

    public function likeDislike(Request $request, $status, $comment_id)
    {
        $validator = Validator::make(
            [
                'status' => $status,
                'comment_id' => $comment_id
            ],
            [
                'status' => ['required', Rule::in(['like', 'dislike'])],
                'comment_id' => ['bail', 'required', 'integer', 'exists:comments,id', new CheckIfCommentOwnerActiveRule()],
            ],
            [
                'comment_id.exists' => __('validation.api.the-selected-comment-id-does-not-exists'),
                'comment_id.required' => __('validation.api.the-comment-id-required'),
                'comment_id.integer' => __('validation.api.comment-id-must-be-integer'),
                'status.required' => __('validation.api.the-status-required'),
                'status.in' => __('validation.api.the-status-invalid'),
            ]
        );

        if ($validator->fails()) {
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $validator->errors()->messages());
        }

        try {
            $this->commentApiUseCase->commentLike($validator->validated());
            return ApiResponse::sendResponse(200, __('app.api.the-status-change-successfully'), []);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);

            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $e->getMessage());
        }
    }
}

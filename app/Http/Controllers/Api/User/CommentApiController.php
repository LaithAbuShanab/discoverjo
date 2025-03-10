<?php

namespace App\Http\Controllers\Api\User;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\User\Post\CreateCommentRequest;
use App\Rules\CheckIfCommentBelongToUser;
use App\Rules\CheckIfUserCanDeleteComment;
use App\UseCases\Api\User\CommentApiUseCase;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class CommentApiController extends Controller
{
    protected $commentApiUseCase;

    public function __construct(CommentApiUseCase $commentUseCase) {

        $this->commentApiUseCase = $commentUseCase;

    }
    /**
     * Display a listing of the resource.
     */
    public function commentStore(CreateCommentRequest $request)
    {
        try{
            $comments = $this->commentApiUseCase->createComment($request->validated());
            return ApiResponse::sendResponse(200,  __('app.api.comment-created-successfully'), []);
        } catch (\Exception $e) {
            return ApiResponse::sendResponse(Response::HTTP_BAD_REQUEST, __("validation.api.something-went-wrong"), $e->getMessage());
        }
    }

    public function commentUpdate(Request $request)
    {
        $id = $request->comment_id;
        $content = $request->content;
        $validator = Validator::make(
            [
                'comment_id' => $id,
                'content' => $content
            ],
            [
                'comment_id' => ['required', 'exists:comments,id', new CheckIfCommentBelongToUser()],
                'content' => ['required', 'string']
            ],
            [
            'comment_id.exists' => __('validation.api.the-selected-comment-id-does-not-exists'),
            'comment_id.required'=> __('validation.api.the-comment-id-required'),
            'content.required'=> __('validation.api.the-content-required'),
        ]);



        if ($validator->fails()) {
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $validator->errors()->messages());
        }
        try{
            $comments = $this->commentApiUseCase->updateComment($validator->validated());
            return ApiResponse::sendResponse(200,  __('app.api.comment-updated-successfully'), []);
        } catch (\Exception $e) {
            return ApiResponse::sendResponse(Response::HTTP_BAD_REQUEST, __("validation.api.something-went-wrong"), $e->getMessage());
        }
    }

    public function commentDelete(Request $request){
        $id = $request->comment_id;

        $validator = Validator::make(
            [
                'comment_id' => $id,
            ],
            [
                'comment_id' => ['required', 'exists:comments,id', new CheckIfUserCanDeleteComment()],
            ],
            [
                'comment_id.exists' => __('validation.api.the-selected-comment-id-does-not-exists'),
                'comment_id.required'=> __('validation.api.the-comment-id-required'),
            ]);
        if ($validator->fails()) {
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $validator->errors()->messages());
        }
        try{
            $comments = $this->commentApiUseCase->deleteComment($id);
            return ApiResponse::sendResponse(200, __('app.api.comment-deleted-successfully'), []);
        } catch (\Exception $e) {
            return ApiResponse::sendResponse(Response::HTTP_BAD_REQUEST, __("validation.api.something-went-wrong"), $e->getMessage());
        }
    }

    public function likeDislike(Request $request)
    {
        $validator = Validator::make(
            ['status' => $request->status, 'comment_id' => $request->comment_id,],
            [
                'status' => ['required', Rule::in(['like', 'dislike'])],
                'comment_id' => ['required', 'integer', 'exists:comments,id'],
            ],
            [
                'comment_id.exists' => __('validation.api.the-selected-comment-id-does-not-exists'),
                'comment_id.required'=> __('validation.api.the-comment-id-required'),
                'status'=>__('validation.api.the-status-required')
            ]);

        if ($validator->fails()) {
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $validator->errors()->messages());
        }

        try {
            $this->commentApiUseCase->commentLike($request);
            return ApiResponse::sendResponse(200, __('app.the-status-change-successfully'), []);
        } catch (\Exception $e) {
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $e->getMessage());
        }
    }
}

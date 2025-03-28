<?php

namespace App\Http\Controllers\Api\User;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\User\Post\CreatePostApiRequest;
use App\Http\Requests\Api\User\Post\UpdatePostApiRequest;
use App\Models\Post;
use App\Rules\CheckIfExistsInFavoratblesRule;
use App\Rules\CheckIfFollowerFollowingExistsRule;
use App\Rules\CheckIfNotExistsInFavoratblesRule;
use App\Rules\CheckIfPostCreateorActiveRule;
use App\Rules\CheckIfUserActiveRule;
use App\Rules\CheckMediaBelongsToUserRule;
use App\Rules\CheckPostBelongToUser;
use App\UseCases\Api\User\PostApiUseCase;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class PostApiController extends Controller
{
    protected $postApiUseCase;

    public function __construct(PostApiUseCase $postApiUseCase)
    {

        $this->postApiUseCase = $postApiUseCase;
    }
    /**
     * Display a listing of the resource.
     */
    public function followingPost()
    {
        try {
            $createTrip = $this->postApiUseCase->followingPost();
            return ApiResponse::sendResponse(200, __('app.api.post-retrieved-successfully'), $createTrip);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $e->getMessage());
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreatePostApiRequest $request)
    {
        $validatedData = $request->validated();
        try {
            $createTrip = $this->postApiUseCase->createPost($validatedData);
            return ApiResponse::sendResponse(200, __('app.api.post-created-successfully'), $createTrip);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $e->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePostApiRequest $request,$post_id)
    {
        $validator = Validator::make(['post_id' => $post_id], [
            'post_id' => ['required', 'exists:posts,id', new CheckPostBelongToUser()]
        ],[
            'post_id.required'=>__('validation.api.guide-trip-id-required'),
            'post_id.exists'=>__('validation.api.guide-trip-id-does-not-exists'),
        ]);
        $validatedData = array_merge($request->validated(), $validator->validated());
        try {
            $createTrip = $this->postApiUseCase->updatePost($validatedData);
            return ApiResponse::sendResponse(200, __('app.api.post-updated-successfully'), $createTrip);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $e->getMessage());
        }
    }

    public function show(Request $request,$post_id)
    {
        $validator = Validator::make(['post_id' => $post_id], [
            'post_id' => ['required', 'exists:posts,id'],
        ],
            [
                'post_id.exists'=>__('validation.api.post-id-invalid'),
                'post_id.required'=>__('validation.api.post-id-does-not-exists')
            ]);

        if ($validator->fails()) {
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $validator->errors()->messages()['post_id'][0]);
        }

        try {
            $post = $this->postApiUseCase->showPost($validator->validated());

            return ApiResponse::sendResponse(200, __('app.api.post-retrieved-successfully'), $post);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $e->getMessage());
        }
    }

    public function DeleteImage(Request $request,$media_id)
    {
        $validator = Validator::make(['media_id' => $media_id], [
            'media_id' => ['required', 'exists:media,id',new CheckMediaBelongsToUserRule()],
        ],[
            'media_id.required'=>__('validation.api.media-id-required'),
            'media_id.exists'=>__('validation.api.media-id-does-not-exists'),
        ]);

        if ($validator->fails()) {
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $validator->errors()->messages()['media_id'][0]);
        }

        try {
            $createTrip = $this->postApiUseCase->deleteImage($validator->validated()['media_id']);
            return ApiResponse::sendResponse(200, __('app.api.trip-image-deleted-successfully'), $createTrip);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request,$post_id)
    {
        $validator = Validator::make(['post_id' => $post_id], [
            'post_id' => ['required', 'exists:posts,id', new CheckPostBelongToUser()],
        ]);

        if ($validator->fails()) {
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $validator->errors()->messages()['post_id'][0]);
        }

        try {
            $createTrip = $this->postApiUseCase->delete($validator->validated()['post_id']);
            return ApiResponse::sendResponse(200, __('app.post-deleted-successfully'), $createTrip);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $e->getMessage());
        }
    }

    public function createFavoritePost(Request $request,$post_id)
    {

        $validator = Validator::make(
            ['post_id' => $post_id],
            [
                'post_id' => ['required', 'exists:posts,id', function ($attribute, $value, $fail) { // Add $attribute and $fail parameters
                        $exists = DB::table('favorables')
                            ->where('user_id', Auth::guard('api')->id())
                            ->where('favorable_type', 'App\Models\Post')
                            ->where('favorable_id', $value)
                            ->exists();

                        if ($exists) {
                            $fail(__('validation.api.you-already-make-this-as-favorite'));
                        }
                    },new CheckIfPostCreateorActiveRule()
                ]
            ],
            [
                'post_id.exists' => __('validation.api.post-id-invalid'),
                'post_id.required' => __('validation.api.post-id-does-not-exists')
            ]
        );

        if ($validator->fails()) {
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $validator->errors()->messages()['post_id'][0]);
        }

        try {
            $createFavPlace = $this->postApiUseCase->createFavoritePost($validator->validated()['post_id']);

            return ApiResponse::sendResponse(200, __('app.api.favorite-post-created-successfully'), $createFavPlace);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $e->getMessage());
        }
    }

    public  function deleteFavoritePost(Request $request,$post_id)
    {

        $validator = Validator::make(
            ['post_id' => $post_id],
            [
                'post_id' => [
                    'required',
                    'exists:posts,id',
                    function ($attribute, $value, $fail) { // Add $attribute and $fail parameters
                        $exists = DB::table('favorables')
                            ->where('user_id', Auth::guard('api')->id())
                            ->where('favorable_type', 'App\Models\Post')
                            ->where('favorable_id', $value)
                            ->exists();

                        if (!$exists) {
                            $fail(__('validation.api.this_is_not_in_favorite_list'));
                        }
                    }
                ]
            ],
            [
                'post_id.exists'=>__('validation.api.post-id-invalid'),
                'post_id.required'=>__('validation.api.post-id-does-not-exists')
            ]);

        if ($validator->fails()) {
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $validator->errors()->messages()['post_id'][0]);
        }

        try {
            $deleteFavPlace = $this->postApiUseCase->deleteFavoritePost($validator->validated()['post_id']);
            return ApiResponse::sendResponse(200, __('app.api.favorite-post-deleted-successfully'), $deleteFavPlace);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);
            return ApiResponse::sendResponse(Response::HTTP_BAD_REQUEST, __("validation.api.something-went-wrong"), $e->getMessage());
        }
    }

    public function likeDislike(Request $request,$status,$post_id)
    {
        $validator = Validator::make(
            ['status' => $status, 'post_id' => $post_id],
            ['status' => ['required', Rule::in(['like', 'dislike'])], 'post_id' => ['required', 'integer', 'exists:posts,id',new CheckIfPostCreateorActiveRule()],],
            [
                'post_id.exists'=>__('validation.api.post-id-invalid'),
                'post_id.required'=>__('validation.api.post-id-does-not-exists'),
                'status'=>__('validation.api.the-status-required')
            ]);

        if ($validator->fails()) {
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $validator->errors()->messages());
        }

        try {
            $this->postApiUseCase->postLike($validator->validated());
            return ApiResponse::sendResponse(200,__('app.event.api.the-likable-status-change-successfully'), []);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $e->getMessage());
        }
    }

    public function currentUserPosts()
    {
        try {
            $posts = $this->postApiUseCase->currentUserPosts();
            return ApiResponse::sendResponse(200, __('app.post-retrieved-successfully'), $posts);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $e->getMessage());
        }

    }

    public function otherUserPosts($slug)
    {
        $validator = Validator::make(['slug' => $slug], [
            'slug' => ['required', 'exists:users,slug',new CheckIfUserActiveRule()],
        ],
            [
                'slug.required'=>__('validation.api.the-user-id-is-required'),
                'slug.exists'=>__('validation.api.the-user-id-does-not-exists'),
            ]
        );

        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $errors);
        }
        try {
            $posts = $this->postApiUseCase->otherUserPosts($validator->validated()['slug']);
            return ApiResponse::sendResponse(200, __('app.post-retrieved-successfully'), $posts);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $e->getMessage());
        }

    }
}

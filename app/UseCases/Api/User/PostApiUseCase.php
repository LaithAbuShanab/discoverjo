<?php

namespace App\UseCases\Api\User;

use App\Interfaces\Gateways\Api\User\PostApiRepositoryInterface;
use Illuminate\Support\Facades\Auth;

class PostApiUseCase
{
    protected $postRepository;

    public function __construct(PostApiRepositoryInterface $postRepository)
    {
        $this->postRepository = $postRepository;
    }

    public function followingPost()
    {
        return $this->postRepository->followingPost();

    }

    public function createPost($validatedData)
    {
        switch ($validatedData['visitable_type']){
            case "place":
                $validatedData['visitable_type']='App\Models\Place';
                break;
            case "plan":
                $validatedData['visitable_type']='App\Models\Plan';
                break;
            case "event":
                $validatedData['visitable_type']='App\Models\Event';
                break;
            case "volunteering":
                $validatedData['visitable_type']='App\Models\Volunteering';
                break;
        }

        $visitable = $validatedData['visitable_type']::findBySlug( $validatedData['visitable_slug']);
        return $this->postRepository->createPost(
            [
                'user_id' => Auth::guard('api')->user()->id,
                'visitable_type' => $validatedData['visitable_type'],
                'visitable_id' => $visitable->id,
                'content' => $validatedData['content'],
                'privacy' => $validatedData['privacy'],
            ],
            isset($validatedData['media']) ? $validatedData['media'] : null,
        );
    }

    public function updatePost($validatedData)
    {
        switch ($validatedData['visitable_type']){
            case "place":
                $validatedData['visitable_type']='App\Models\Place';
                break;
            case "plan":
                $validatedData['visitable_type']='App\Models\Plan';
                break;
            case "event":
                $validatedData['visitable_type']='App\Models\Event';
                break;
            case "volunteering":
                $validatedData['visitable_type']='App\Models\Volunteering';
                break;
        }
        $visitable = $validatedData['visitable_type']::findBySlug( $validatedData['visitable_slug']);
        return $this->postRepository->updatePost(
            [
                'user_id' => Auth::guard('api')->user()->id,
                'visitable_type' => $validatedData['visitable_type'],
                'visitable_id' =>$visitable->id,
                'content' => $validatedData['content'],
                'privacy' => $validatedData['privacy'],
                'post_id' => $validatedData['post_id'],
                'seen_status'=>0
            ],
            isset($validatedData['media']) ? $validatedData['media'] : null,
        );
    }

    public function showPost($request)
    {
        $id = $request['post_id'];
        return $this->postRepository->showPost($id);
    }

    public function deletePost($id)
    {
        return $this->postRepository->deletePost($id);
    }

    public function deleteImage($id)
    {
        return $this->postRepository->deleteImage($id);
    }

    public function delete($id)
    {
        return $this->postRepository->delete($id);
    }

    public function allPosts()
    {
        return $this->postRepository->allPosts();
    }

    public function createFavoritePost($id)
    {
        return $this->postRepository->favorite($id);
    }

    public function deleteFavoritePost($id)
    {
        return $this->postRepository->deleteFavorite($id);
    }

    public function postLike($data)
    {
        return $this->postRepository->postLike($data);
    }
}

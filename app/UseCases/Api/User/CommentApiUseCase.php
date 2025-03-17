<?php

namespace App\UseCases\Api\User;


use App\Interfaces\Gateways\Api\User\CommentApiRepositoryInterface;
use Illuminate\Support\Facades\Auth;

class CommentApiUseCase
{
    protected $commentRepository;

    public function __construct(CommentApiRepositoryInterface $commentRepository)
    {
        $this->commentRepository = $commentRepository;
    }

    public function createComment($data)
    {
        return $this->commentRepository->createComment([
            'user_id'=>Auth::guard('api')->user()->id,
            'post_id'=>$data['post_id'],
            'content'=>$data['content'],
            'parent_id'=>$data['parent_id']
        ]);

    }

    public function updateComment($data)
    {
        return $this->commentRepository->updateComment($data);
    }

    public function deleteComment($id)
    {
        return $this->commentRepository->deleteComment($id);
    }

    public function commentLike($data)
    {
        return $this->commentRepository->commentLike($data);
    }




}

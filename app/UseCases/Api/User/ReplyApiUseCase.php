<?php

namespace App\UseCases\Api\User;


use App\Interfaces\Gateways\Api\User\ReplyApiRepositoryInterface;
use Illuminate\Support\Facades\Auth;

class ReplyApiUseCase
{
    protected $replyRepository;

    public function __construct(ReplyApiRepositoryInterface $replyRepository)
    {
        $this->replyRepository = $replyRepository;
    }

    public function createReply($data)
    {
        return $this->replyRepository->createReply([
            'user_id'=>Auth::guard('api')->user()->id,
            'comment_id'=>$data['comment_id'],
            'content'=>$data['content']
        ]);
    }

    public function updateReply($data)
    {
        return $this->replyRepository->updateReply($data);
    }

    public function deleteReply($id)
    {
        return $this->replyRepository->deleteReply($id);
    }

    public function replyLike($data)
    {
        return $this->replyRepository->replyLike($data);
    }




}

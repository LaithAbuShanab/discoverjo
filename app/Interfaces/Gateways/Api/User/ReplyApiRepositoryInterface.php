<?php

namespace App\Interfaces\Gateways\Api\User;


interface ReplyApiRepositoryInterface
{
    public function createReply($data);
    public function updateReply($data);
    public function DeleteReply($id);
    public function replyLike($data);


}

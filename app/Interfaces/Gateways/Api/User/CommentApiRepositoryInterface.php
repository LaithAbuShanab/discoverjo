<?php

namespace App\Interfaces\Gateways\Api\User;


interface CommentApiRepositoryInterface
{
    public function createComment($data);
    public function updateComment($data);
    public function DeleteComment($id);
    public function commentLike($data);


}

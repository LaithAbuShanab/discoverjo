<?php

namespace App\Interfaces\Gateways\Api\User;


interface GroupChatRepositoryInterface
{
    public function getGroupMessages($conversationId);

    public function getGroupMembers($conversationId);

    public function sendMessages($request);
}

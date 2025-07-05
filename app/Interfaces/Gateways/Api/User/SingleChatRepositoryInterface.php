<?php

namespace App\Interfaces\Gateways\Api\User;


interface SingleChatRepositoryInterface
{
    public function createSingleChat($slug);

    public function listConversations();

    public function getConversation($conversation_id);

    public function sendMessages($request);
}

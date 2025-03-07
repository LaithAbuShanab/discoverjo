<?php

namespace App\UseCases\Api\User;

use App\Interfaces\Gateways\Api\User\GroupChatRepositoryInterface;

class GroupChatUseCase
{
    protected $groupChaRepository;

    public function __construct(GroupChatRepositoryInterface $groupChaRepository)
    {
        $this->groupChaRepository = $groupChaRepository;
    }

    public function getGroupMessages($conversationId)
    {
        return $this->groupChaRepository->getGroupMessages($conversationId);
    }

    public function getGroupMembers($conversationId)
    {
        return $this->groupChaRepository->getGroupMembers($conversationId);
    }

    public function sendMessages($request)
    {
        return $this->groupChaRepository->sendMessages($request);
    }
}

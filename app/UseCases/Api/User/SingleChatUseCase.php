<?php

namespace App\UseCases\Api\User;

use App\Interfaces\Gateways\Api\User\SingleChatRepositoryInterface;

class SingleChatUseCase
{
    public function __construct(protected SingleChatRepositoryInterface $singleChaRepository)
    {
        $this->singleChaRepository = $singleChaRepository;
    }

    public function createSingleChat($slug)
    {
        return $this->singleChaRepository->createSingleChat($slug);
    }

    public function listConversations()
    {
        return $this->singleChaRepository->listConversations();
    }

    public function getConversation($conversation_id)
    {
        return $this->singleChaRepository->getConversation($conversation_id);
    }

    public function sendMessages($request)
    {
        return $this->singleChaRepository->sendMessages($request);
    }
}

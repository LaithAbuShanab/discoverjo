<?php

namespace App\UseCases\Api\User;


use App\Interfaces\Gateways\Api\User\BlockUserApiRepositoryInterface;

class BlockUserApiUseCase
{
    public function __construct(protected BlockUserApiRepositoryInterface $userBlockRepository) {}

    public function listOfBlockedUsers()
    {
        return $this->userBlockRepository->listOfBlockedUsers();
    }

    public function block($slug)
    {
        return $this->userBlockRepository->block($slug);
    }

    public function unblock($slug)
    {
        return $this->userBlockRepository->unblock($slug);
    }
}

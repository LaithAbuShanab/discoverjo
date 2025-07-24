<?php

namespace App\UseCases\Api\User;


use App\Interfaces\Gateways\Api\User\BlockUserApiRepositoryInterface;

class BlockUserApiUseCase
{
    public function __construct(protected BlockUserApiRepositoryInterface $userBlockRepository)
    {
        $this->userBlockRepository = $userBlockRepository;
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

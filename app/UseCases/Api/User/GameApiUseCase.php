<?php

namespace App\UseCases\Api\User;

use App\Interfaces\Gateways\Api\User\GameApiRepositoryInterface;

class GameApiUseCase
{
    protected $gameApiRepository;

    public function __construct(GameApiRepositoryInterface $gameApiRepository)
    {
        $this->gameApiRepository = $gameApiRepository;
    }

    public function start()
    {
        return $this->gameApiRepository->start();
    }

    public function next($data)
    {
        return $this->gameApiRepository->next($data);
    }

    public function finish()
    {
        return $this->gameApiRepository->finish();
    }


}

<?php

namespace App\UseCases\Api\User;

use App\Interfaces\Gateways\Api\User\RegionsApiRepositoryInterface;
use Illuminate\Support\Facades\Auth;

class RegionsApiUseCase
{
    protected $RegionsRepository;

    public function __construct(RegionsApiRepositoryInterface $RegionsRepository)
    {
        $this->RegionsRepository = $RegionsRepository;
    }

    public function allRegions()
    {
        return $this->RegionsRepository->allRegions();
    }
}

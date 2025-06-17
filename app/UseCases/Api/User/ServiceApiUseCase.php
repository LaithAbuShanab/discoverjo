<?php

namespace App\UseCases\Api\User;

use App\Interfaces\Gateways\Api\User\ServiceApiRepositoryInterface;

class ServiceApiUseCase
{
    protected $serviceApiRepository;

    public function __construct(ServiceApiRepositoryInterface $serviceApiRepository)
    {
        $this->serviceApiRepository = $serviceApiRepository;
    }

    public function allServices()
    {
        return $this->serviceApiRepository->allServices();
    }


}

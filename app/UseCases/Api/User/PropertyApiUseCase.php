<?php

namespace App\UseCases\Api\User;

use App\Interfaces\Gateways\Api\User\PropertyApiRepositoryInterface;
use App\Interfaces\Gateways\Api\User\LegalDocumentApiRepositoryInterface;

class PropertyApiUseCase
{
    protected $propertyApiRepository;

    public function __construct(PropertyApiRepositoryInterface $propertyApiRepository)
    {
        $this->propertyApiRepository = $propertyApiRepository;
    }

    public function getAllChalets()
    {
        return $this->propertyApiRepository->getAllChalets();
    }
    public function singleProperty($slug)
    {
        return $this->propertyApiRepository->singleProperty($slug);
    }



}

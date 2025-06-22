<?php

namespace App\UseCases\Api\User;


use App\Interfaces\Gateways\Api\User\PropertyReservationApiRepositoryInterface;
use Illuminate\Support\Facades\Auth;

class PropertyReservationApiUseCase
{
    protected $propertyReservationApiRepository;

    public function __construct(PropertyReservationApiRepositoryInterface $propertyReservationApiRepository)
    {
        $this->propertyReservationApiRepository = $propertyReservationApiRepository;
    }

    public function checkAvailable($data)
    {
        return $this->propertyReservationApiRepository->checkAvailable($data);
    }



}

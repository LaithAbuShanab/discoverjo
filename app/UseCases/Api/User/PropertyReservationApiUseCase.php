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

    public function checkAvailableMonth($data)
    {
        return $this->propertyReservationApiRepository->checkAvailableMonth($data);
    }

    public function CheckPrice($data)
    {
        return $this->propertyReservationApiRepository->CheckPrice($data);
    }

    public function makeReservation($data)
    {
        return $this->propertyReservationApiRepository->makeReservation($data);
    }

    public function updateReservation($data)
    {
        return $this->propertyReservationApiRepository->updateReservation($data);
    }

    public function deleteReservation($id)
    {
        return $this->propertyReservationApiRepository->deleteReservation($id);
    }

    public function allPropertyReservations($slug)
    {
        return $this->propertyReservationApiRepository->allPropertyReservations($slug);
    }

    public function allReservations()
    {
        return $this->propertyReservationApiRepository->allReservations();
    }
    public function changeStatusReservation($data)
    {
        return $this->propertyReservationApiRepository->changeStatusReservation($data);
    }
    public function RequestReservations($slug)
    {
        return $this->propertyReservationApiRepository->RequestReservations($slug);
    }
    public function approvedRequestReservations($slug)
    {
        return $this->propertyReservationApiRepository->approvedRequestReservations($slug);
    }

}

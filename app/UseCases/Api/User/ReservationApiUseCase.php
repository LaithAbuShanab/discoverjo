<?php

namespace App\UseCases\Api\User;

use App\Interfaces\Gateways\Api\User\GuideTripApiRepositoryInterface;
use App\Interfaces\Gateways\Api\User\ReservationApiRepositoryInterface;
use App\Interfaces\Gateways\Api\User\ServiceApiRepositoryInterface;
use App\Models\Region;
use Illuminate\Support\Facades\Auth;

class ReservationApiUseCase
{
    protected $reservationApiRepository;

    public function __construct(ReservationApiRepositoryInterface $reservationApiRepository)
    {
        $this->reservationApiRepository = $reservationApiRepository;
    }

    public function reservationDate($data)
    {
        return $this->reservationApiRepository->reservationDate($data);
    }

    public function serviceReservation($data)
    {
        return $this->reservationApiRepository->serviceReservation($data);
    }

    public function UserServiceReservations($data)
    {
        return $this->reservationApiRepository->UserServiceReservations($data);
    }

    public function allReservations()
    {
        return $this->reservationApiRepository->allReservations();
    }

    public function deleteReservation($id)
    {
        return $this->reservationApiRepository->deleteReservation($id);
    }

    public function updateReservation($data)
    {
        return $this->reservationApiRepository->updateReservation($data);
    }

    public function changeStatusReservation($data)
    {
        return $this->reservationApiRepository-> changeStatusReservation($data);
    }

    public function providerRequestReservations($slug)
    {
        return $this->reservationApiRepository-> providerRequestReservations($slug);
    }

    public function approvedRequestReservations($slug)
    {
        return $this->reservationApiRepository-> approvedRequestReservations($slug);
    }

}

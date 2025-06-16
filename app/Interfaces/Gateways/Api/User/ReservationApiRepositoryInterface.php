<?php

namespace App\Interfaces\Gateways\Api\User;

interface ReservationApiRepositoryInterface
{
    public function reservationDate($data);
    public function serviceReservation($data);
    public function UserServiceReservations($data);
    public function allReservations();
    public function deleteReservation($id);
    public function updateReservation($data);
    public function changeStatusReservation($data);
    public function providerRequestReservations($slug);
    public function approvedRequestReservations($slug);


}

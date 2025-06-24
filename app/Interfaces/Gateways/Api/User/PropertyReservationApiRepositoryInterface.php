<?php

namespace App\Interfaces\Gateways\Api\User;

interface PropertyReservationApiRepositoryInterface
{
    public function checkAvailable($data);
    public function checkAvailableMonth($data);

    public function CheckPrice($data);
    public function makeReservation($data);
    public function updateReservation($data);

    public function deleteReservation($id);
    public function allPropertyReservations($slug);
    public function allReservations();
    public function changeStatusReservation($data);
    public function RequestReservations($slug);
    public function approvedRequestReservations($slug);


}

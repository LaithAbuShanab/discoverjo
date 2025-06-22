<?php

namespace App\Interfaces\Gateways\Api\User;

interface PropertyReservationApiRepositoryInterface
{
    public function checkAvailable($data);
    public function checkAvailableMonth($data);


}

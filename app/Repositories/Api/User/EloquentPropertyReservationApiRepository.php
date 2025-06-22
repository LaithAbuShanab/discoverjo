<?php

namespace App\Repositories\Api\User;


use App\Interfaces\Gateways\Api\User\PropertyReservationApiRepositoryInterface;
use App\Models\Service;
use App\Models\ServiceReservationDetail;
use Illuminate\Support\Carbon;


class EloquentPropertyReservationApiRepository implements PropertyReservationApiRepositoryInterface
{
    public function checkAvailable($data)
    {
        dd($data);
    }


}

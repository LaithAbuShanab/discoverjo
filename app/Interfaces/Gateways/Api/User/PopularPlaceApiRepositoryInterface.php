<?php

namespace App\Interfaces\Gateways\Api\User;


interface PopularPlaceApiRepositoryInterface
{


    public function popularPlaces($data);
    public function search($query);

}

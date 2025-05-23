<?php

namespace App\Interfaces\Gateways\Api\User;


interface PopularPlaceApiRepositoryInterface
{


    public function popularPlaces();
    public function search($data);

}

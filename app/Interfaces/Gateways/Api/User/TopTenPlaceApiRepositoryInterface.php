<?php

namespace App\Interfaces\Gateways\Api\User;


interface TopTenPlaceApiRepositoryInterface
{


    public function topTenPlaces();
    public function search($data);

}

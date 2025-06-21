<?php

namespace App\Interfaces\Gateways\Api\User;


interface PropertyApiRepositoryInterface
{
    public function getAllChalets();
    public function singleProperty($slug);
}

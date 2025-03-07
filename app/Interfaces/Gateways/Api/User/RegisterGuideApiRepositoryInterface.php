<?php

namespace App\Interfaces\Gateways\Api\User;


interface RegisterGuideApiRepositoryInterface
{
    public function register(array $userData,$token,$tags,$userImage,$userFile);

}

<?php

namespace App\Interfaces\Gateways\Api\User;


interface ContactUsApiRepositoryInterface
{
    public function createContactUs($data,$imageData);

}

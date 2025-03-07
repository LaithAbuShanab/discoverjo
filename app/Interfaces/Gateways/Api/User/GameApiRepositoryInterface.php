<?php

namespace App\Interfaces\Gateways\Api\User;


interface GameApiRepositoryInterface
{
    public function start();
    public function next($data);
    public function finish();

}

<?php

namespace App\Interfaces\Gateways\Api\User;


interface BlockUserApiRepositoryInterface
{
    public function block($slug);
    public function unblock($slug);
}

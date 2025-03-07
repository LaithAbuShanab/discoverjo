<?php

namespace App\Interfaces\Gateways\Api\User;


interface AuthApiRepositoryInterface
{
    public function register(array $userData);

    public function login($userData);

    public function logout();

    public function deleteAccount();

    public function deactivateAccount();
}

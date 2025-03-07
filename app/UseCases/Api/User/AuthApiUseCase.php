<?php

namespace App\UseCases\Api\User;


use App\Interfaces\Gateways\Api\User\AuthApiRepositoryInterface;
use Illuminate\Support\Facades\Auth;

class AuthApiUseCase
{
    protected $authRepository;

    public function __construct(AuthApiRepositoryInterface $authRepository)
    {
        $this->authRepository = $authRepository;
    }

    public function register($request, $lang)
    {
        $request['lang'] = $lang;
        return $this->authRepository->register($request);
    }

    public function login($request)
    {
        return $this->authRepository->login($request);
    }

    public function logout()
    {
        return $this->authRepository->logout();
    }

    public function deleteAccount()
    {
        return $this->authRepository->deleteAccount();
    }

    public function deactivateAccount()
    {
        return $this->authRepository->deactivateAccount();
    }
}

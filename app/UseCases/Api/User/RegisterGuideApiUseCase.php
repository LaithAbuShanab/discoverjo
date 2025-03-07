<?php

namespace App\UseCases\Api\User;


use App\Interfaces\Gateways\Api\User\RegisterGuideApiRepositoryInterface;

class RegisterGuideApiUseCase
{
    protected $registerGuideApiRepository;

    public function __construct(RegisterGuideApiRepositoryInterface $registerGuideApiRepository)
    {
        $this->registerGuideApiRepository = $registerGuideApiRepository;
    }

    public function register($request, $lang)
    {

        return $this->registerGuideApiRepository->register([
            'first_name'=>$request['first_name'],
            'last_name'=>$request['last_name'],
            'username'=>$request['username'],
            'password'=>$request['password'],
            'birthday'=>$request['birthday'],
            'sex'=>$request['gender'],
            'email'=>$request['email'],
            'description'=>$request['description'],
            'phone_number'=>$request['phone_number'],
            'status'=>0,
            'is_guide'=>1,
            'lang'=>$lang

        ],$request['device_token'],json_decode($request['tags_id']),isset($request['image']) ? $request['image'] : null,isset($request['professional_file'])?$request['professional_file']:null);
    }


}

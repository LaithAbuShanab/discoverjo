<?php

namespace App\UseCases\Api\User;

use App\Interfaces\Gateways\Api\User\ContactUsApiRepositoryInterface;
use App\Interfaces\Gateways\Api\User\LegalDocumentApiRepositoryInterface;

class ContactUsApiUseCase
{
    protected $contactUsApiRepositoryInterface;

    public function __construct(ContactUsApiRepositoryInterface $contactUsApiRepositoryInterface)
    {
        $this->contactUsApiRepositoryInterface = $contactUsApiRepositoryInterface;
    }



    public function createContactUs($request)
    {
        return $this->contactUsApiRepositoryInterface->createContactUs([
            'name'=>$request['name'],
            'email'=>$request['email'],
            'subject'=>$request['subject'],
            'message'=>$request['message'],

        ],isset($request['images']) ? $request['images'] : null);
    }


}

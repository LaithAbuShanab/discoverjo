<?php

namespace App\UseCases\Api\User;

use App\Interfaces\Gateways\Api\User\LegalDocumentApiRepositoryInterface;

class LegalDocumentApiUseCase
{
    protected $legalDocumentApiRepository;

    public function __construct(LegalDocumentApiRepositoryInterface $legalDocumentApiRepository)
    {
        $this->legalDocumentApiRepository = $legalDocumentApiRepository;
    }



    public function getAllLegalDocument()
    {
        return $this->legalDocumentApiRepository->getAllLegalDocument();
    }


}

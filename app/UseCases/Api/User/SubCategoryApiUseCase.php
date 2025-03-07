<?php

namespace App\UseCases\Api\User;

use App\Interfaces\Gateways\Api\User\SubCategoryApiRepositoryInterface;

class SubCategoryApiUseCase
{
    protected $subCategoryApiRepository;

    public function __construct(SubCategoryApiRepositoryInterface $subCategoryApiRepository)
    {
        $this->subCategoryApiRepository = $subCategoryApiRepository;
    }

    public function singleSubCategory($id)
    {
        return $this->subCategoryApiRepository->singleSubCategory($id);
    }
}

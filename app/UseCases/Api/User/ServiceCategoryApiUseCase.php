<?php

namespace App\UseCases\Api\User;


use App\Interfaces\Gateways\Api\User\ServiceCategoryApiRepositoryInterface;


class ServiceCategoryApiUseCase
{
    protected $serviceCategoryApiRepository;

    public function __construct(ServiceCategoryApiRepositoryInterface $serviceCategoryApiRepository)
    {
        $this->serviceCategoryApiRepository = $serviceCategoryApiRepository;
    }



    public function allServiceCategories()
    {

        return $this->serviceCategoryApiRepository->allServiceCategories();
    }

    public function allServiceByCategory($data)
    {

        return $this->serviceCategoryApiRepository->allServiceByCategory($data);
    }

}

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

    public function allSubcategories($data)
    {
        return $this->serviceCategoryApiRepository->allSubcategories($data);
    }
    public function search($query)
    {
        return $this->serviceCategoryApiRepository->search($query);
    }
    public function dateServices($date)
    {
        return $this->serviceCategoryApiRepository->dateServices($date);
    }

    public function singleService($slug)
    {
        return $this->serviceCategoryApiRepository->singleService($slug);
    }

}

<?php

namespace App\Interfaces\Gateways\Api\User;

interface ServiceCategoryApiRepositoryInterface
{
    public function allServiceCategories();
    public function allServiceByCategory($data);

}

<?php

namespace App\Interfaces\Gateways\Api\User;

interface ServiceCategoryApiRepositoryInterface
{
    public function allServiceCategories();
    public function allServiceByCategory($data);
    public function allSubcategories($data);
    public function search($query);
    public function dateServices($date);
    public function singleService($slug);
    public function servicesBySubcategory($slug);

}

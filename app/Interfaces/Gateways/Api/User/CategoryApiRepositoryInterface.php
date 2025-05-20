<?php

namespace App\Interfaces\Gateways\Api\User;


interface CategoryApiRepositoryInterface
{
    public function getAllCategories();

    public function allSubcategories($data);

    public function shuffleAllCategories();

    public function allPlacesByCategory($data);

    public function search($query);


}

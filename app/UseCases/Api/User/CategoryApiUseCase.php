<?php

namespace App\UseCases\Api\User;


use App\Interfaces\Gateways\Api\User\CategoryApiRepositoryInterface;

class CategoryApiUseCase
{
    public function __construct(protected CategoryApiRepositoryInterface $categoryRepository)
    {
        $this->categoryRepository = $categoryRepository;
    }

    public function allCategories()
    {
        return $this->categoryRepository->getAllCategories();
    }

    public function allSubcategories($data)
    {
        return $this->categoryRepository->allSubcategories($data);
    }

    public function shuffleAllCategories()
    {
        return $this->categoryRepository->shuffleAllCategories();
    }

    public function allPlacesByCategory($data)
    {
        return $this->categoryRepository->allPlacesByCategory($data);
    }

    public function search($query)
    {
        return $this->categoryRepository->search($query);
    }



}

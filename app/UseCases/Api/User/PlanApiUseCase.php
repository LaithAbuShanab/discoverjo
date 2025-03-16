<?php

namespace App\UseCases\Api\User;

use App\Interfaces\Gateways\Api\User\PlanApiRepositoryInterface;

class PlanApiUseCase
{
    protected $planRepository;

    public function __construct(PlanApiRepositoryInterface $planRepository)
    {
        $this->planRepository = $planRepository;
    }

    public function allPlans()
    {
        return $this->planRepository->allPlans();
    }

    public function createPlan($validatedData)
    {
        return $this->planRepository->createPlan($validatedData);
    }

    public function updatePlan($validatedData)
    {
        return $this->planRepository->updatePlan($validatedData);
    }

    public function deletePlan($slug)
    {
        return $this->planRepository->deletePlan($slug);
    }

    public function show($slug)
    {
        return $this->planRepository->show($slug);
    }

    public function myPlans()
    {
        return $this->planRepository->myPlans();
    }

    public function plans()
    {
        return $this->planRepository->plans();
    }

    public function createFavoritePlan($id)
    {
        return $this->planRepository->favorite($id);
    }
    public function deleteFavoritePlan($id)
    {
        return $this->planRepository->deleteFavorite($id);
    }
    public function search($query)
    {
        return $this->planRepository->search($query);
    }

    public function filter($data)
    {
        return $this->planRepository->filter($data);
    }
}

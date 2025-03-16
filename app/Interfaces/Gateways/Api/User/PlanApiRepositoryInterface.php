<?php

namespace App\Interfaces\Gateways\Api\User;


interface PlanApiRepositoryInterface
{
    public function allPlans();

    public function createPlan($validatedData);

    public function updatePlan($validatedData);

    public function deletePlan($slug);

    public function show($slug);

    public function myPlans();

    public function plans();

    public function favorite($id);

    public function deleteFavorite($id);

    public function search($query);

    public function filter($data);
}

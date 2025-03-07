<?php

namespace App\Interfaces\Gateways\Api\User;


interface PlanApiRepositoryInterface
{
    public function allPlans();
    public function plans();
    public function createPlan($validatedData);
    public function updatePlan($validatedData);
    public function deletePlan($id);
    public function show($id);
    public function favorite($id);
    public function deleteFavorite($id);
    public function search($query);
    public function filter($data);
    public function myPlans();
}

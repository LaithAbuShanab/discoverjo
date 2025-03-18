<?php

namespace App\UseCases\Api\User;

use App\Interfaces\Gateways\Api\User\VolunteeringApiRepositoryInterface;
use Illuminate\Support\Facades\Auth;

class VolunteeringApiUseCase
{
    protected $volunteeringRepository;

    public function __construct(VolunteeringApiRepositoryInterface $volunteeringRepository)
    {
        $this->volunteeringRepository = $volunteeringRepository;
    }

    public function allVolunteerings()
    {
        return $this->volunteeringRepository->getAllVolunteerings();
    }

    public function activeVolunteerings()
    {
        return $this->volunteeringRepository->activeVolunteerings();
    }

    public function Volunteering($slug)
    {
        return $this->volunteeringRepository->volunteering($slug);
    }

    public function dateVolunteerings($date)
    {
        return $this->volunteeringRepository->dateVolunteerings($date);
    }

    public function interestVolunteering($slug)
    {
        return $this->volunteeringRepository->createInterestVolunteering($slug);
    }

    public function disinterestVolunteering($slug)
    {
        return $this->volunteeringRepository->disinterestVolunteering($slug);
    }


    public function search($query)
    {
        return $this->volunteeringRepository->search($query);
    }

    public function interestedList($id)
    {
        return $this->volunteeringRepository->interestedList($id);
    }
}

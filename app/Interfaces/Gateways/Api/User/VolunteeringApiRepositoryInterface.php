<?php

namespace App\Interfaces\Gateways\Api\User;


interface VolunteeringApiRepositoryInterface
{
    public function getAllVolunteerings();
    public function activeVolunteerings();
    public function volunteering($slug);
    public function dateVolunteerings($date);
    public function createInterestVolunteering($slug);
    public function disinterestVolunteering($slug);
    public function search($query);
    public function interestedList($id);
}

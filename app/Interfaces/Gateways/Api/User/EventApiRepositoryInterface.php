<?php

namespace App\Interfaces\Gateways\Api\User;


interface EventApiRepositoryInterface
{
    public function getAllEvents();
    public function activeEvents();
    public function event($slug);
    public function dateEvents($date);
    public function createInterestEvent($slug);
    public function disinterestEvent($slug);
    public function search($query);
    public function interestList($id);
}

<?php

namespace App\Interfaces\Gateways\Api\User;


interface SuggestionPlaceApiRepositoryInterface
{
    public function createSuggestionPlace($data,$imageData);

}

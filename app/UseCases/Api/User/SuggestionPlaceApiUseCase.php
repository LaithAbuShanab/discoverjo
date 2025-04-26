<?php

namespace App\UseCases\Api\User;

use App\Interfaces\Gateways\Api\User\SuggestionPlaceApiRepositoryInterface;

class SuggestionPlaceApiUseCase
{
    protected $suggestionPlaceApiRepositoryInterface;

    public function __construct(SuggestionPlaceApiRepositoryInterface $suggestionPlaceApiRepositoryInterface)
    {
        $this->suggestionPlaceApiRepositoryInterface = $suggestionPlaceApiRepositoryInterface;
    }

    public function createSuggestionPlace($request)
    {

        return $this->suggestionPlaceApiRepositoryInterface->createsuggestionPlace([
            'place_name'=>htmlspecialchars(strip_tags($request['place_name'])),
            'address'=>htmlspecialchars(strip_tags($request['address'])),

        ],isset($request['images']) ? $request['images'] : null);
    }


}

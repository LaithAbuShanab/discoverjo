<?php

namespace App\UseCases\Api\User;

use App\Interfaces\Gateways\Api\User\SuggestionPlaceApiRepositoryInterface;
use App\Interfaces\Gateways\Api\User\LegalDocumentApiRepositoryInterface;

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
            'place_name'=>$request['place_name'],
            'address'=>$request['address'],

        ],isset($request['images']) ? $request['images'] : null);
    }


}

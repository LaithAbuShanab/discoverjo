<?php

namespace App\Http\Controllers\Api\User;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\User\Suggestion\StoreSuggestionPlaceApiRequest;
use App\UseCases\Api\User\SuggestionPlaceApiUseCase;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class SuggestionPlaceApiController extends Controller
{
    protected $suggestionPlaceApiUseCase;

    public function __construct(SuggestionPlaceApiUseCase $suggestionPlaceApiUseCase) {

        $this->suggestionPlaceApiUseCase = $suggestionPlaceApiUseCase;

    }

    public function store(StoreSuggestionPlaceApiRequest $request)
    {
        $validatedData = $request->validated();

        try {
            $createTrip = $this->suggestionPlaceApiUseCase->createSuggestionPlace($validatedData);
            return ApiResponse::sendResponse(200, __('app.api.suggestion-place-created-successfully'), $createTrip);
        } catch (\Exception $e) {
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $e->getMessage());
        }

    }
}

<?php

namespace App\Http\Controllers\Api\User;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\User\Game\NextQuestionRequest;
use App\UseCases\Api\User\GameApiUseCase;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class GameApiController extends Controller
{
    public function __construct(protected GameApiUseCase $gameApiUseCase)
    {
        $this->gameApiUseCase = $gameApiUseCase;
    }

    /**
     * Display a listing of the resource.
     */
    public function start()
    {
        try {
            $question = $this->gameApiUseCase->start();
            return ApiResponse::sendResponse(200, __('app.api.first-question-retrieved-successfully'), $question);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST, [$e->getLine(), $e->getMessage()]);
        }
    }

    public function next(NextQuestionRequest $request)
    {
        try {
            $question = $this->gameApiUseCase->next($request->validated());
            return ApiResponse::sendResponse(200, __('app.api.next-question-retrieved-successfully'), $question);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST, $e->getMessage());
        }
    }

    public function finish()
    {
        try {
            $question = $this->gameApiUseCase->finish();
            return ApiResponse::sendResponse(200, __('app.api.the-result-retrieved-successfully'), $question);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST, $e->getMessage());
        }
    }
}

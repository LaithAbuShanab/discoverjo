<?php

namespace App\Http\Controllers\Api\User\RegisterGuide;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Guide\RegisterGuideApiRequest;
use App\UseCases\Api\User\RegisterGuideApiUseCase;
use Illuminate\Http\Response;

class RegisterGuideApiController extends Controller
{
    protected $registerGuideApiUseCase;

    public function __construct(RegisterGuideApiUseCase $registerGuideApiUseCase)
    {
        $this->registerGuideApiUseCase = $registerGuideApiUseCase;
    }

    public function register(RegisterGuideApiRequest $request)
    {
        $lang = $request->header('Content-Language','ar');
        try {
            $user = $this->registerGuideApiUseCase->register($request->validated(), $lang);
            return ApiResponse::sendResponse(200, __('app.auth.api.you-register-successfully'), $user);
        } catch (\Exception $e) {
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $e->getMessage());
        }
    }


}

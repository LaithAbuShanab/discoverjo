<?php

namespace App\Http\Controllers\Api\User;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\User\Contact\StoreContactUsApiRequest;
use App\UseCases\Api\User\ContactUsApiUseCase;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ContactUsApiController extends Controller
{
    protected $contactUsApiUseCase;

    public function __construct(ContactUsApiUseCase $contactUsApiUseCase)
    {

        $this->contactUsApiUseCase = $contactUsApiUseCase;
    }

    public function store(StoreContactUsApiRequest $request)
    {
        $validatedData = $request->validated();

        try {
            $this->contactUsApiUseCase->createContactUs($validatedData);
            return ApiResponse::sendResponse(200, __('app.message-sent-successfully'), []);
        } catch (\Exception $e) {
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $e->getMessage());
        }
    }
}

<?php

namespace App\Http\Controllers\Api\User;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\User\Contact\StoreContactUsApiRequest;
use App\UseCases\Api\User\ContactUsApiUseCase;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class ContactUsApiController extends Controller
{
    public function __construct(protected ContactUsApiUseCase $contactUsApiUseCase)
    {
        $this->contactUsApiUseCase = $contactUsApiUseCase;
    }

    public function store(StoreContactUsApiRequest $request)
    {
        $validatedData = $request->validated();

        try {
            $this->contactUsApiUseCase->createContactUs($validatedData);
            return ApiResponse::sendResponse(200, __('app.api.contact-us-created-successfully'), []);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $e->getMessage());
        }
    }
}

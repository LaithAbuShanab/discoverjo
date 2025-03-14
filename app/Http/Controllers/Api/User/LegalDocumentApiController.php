<?php

namespace App\Http\Controllers\Api\User;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\UseCases\Api\User\LegalDocumentApiUseCase;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class LegalDocumentApiController extends Controller
{
    protected $legalDocumentUseCase;

    public function __construct(LegalDocumentApiUseCase $legalDocumentUseCase) {

        $this->legalDocumentUseCase = $legalDocumentUseCase;

    }
    /**
     * Display a listing of the resource.
     */

    public function index()
    {
        try{
            $legal = $this->legalDocumentUseCase->getAllLegalDocument();
            return ApiResponse::sendResponse(200, __('app.api.legal-retrieved-successfully'), $legal);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);
            return ApiResponse::sendResponse(Response::HTTP_BAD_REQUEST, __("validation.api.something-went-wrong"), $e->getMessage());
        }
    }
}

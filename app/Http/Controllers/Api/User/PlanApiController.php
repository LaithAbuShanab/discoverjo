<?php

namespace App\Http\Controllers\Api\User;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\User\Plan\CreatePlanApiRequest;
use App\Http\Requests\Api\User\Plan\FilterPlanRequest;
use App\Http\Requests\Api\User\Plan\UpdatePlanApiRequest;
use App\Rules\CheckIfExistsInFavoratblesRule;
use App\Rules\CheckIfHasInjectionBasedTimeRule;
use App\Rules\CheckIfNotExistsInFavoratblesRule;
use App\Rules\CheckIfPlanBelongsToUser;
use App\Rules\CheckIfPlanBelongsToUserOrAdmin;
use App\UseCases\Api\User\PlanApiUseCase;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class PlanApiController extends Controller
{
    public function __construct(protected PlanApiUseCase $planApiUseCase)
    {
        $this->planApiUseCase = $planApiUseCase;
    }

    public function index()
    {
        try {
            $plans = $this->planApiUseCase->allPlans();
            return ApiResponse::sendResponse(200, __('app.api.plans-retrieved-successfully'), $plans);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $e->getMessage());
        }
    }

    public function create(CreatePlanApiRequest $request)
    {
        try {
            $validatedData = $request->validated();
            $createPlan = $this->planApiUseCase->createPlan($validatedData);
            return ApiResponse::sendResponse(200, __('app.api.plan-created-successfully'), $createPlan);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $e->getMessage());
        }
    }

    public function update(UpdatePlanApiRequest $request)
    {
        try {
            $validatedData = $request->validated();
            $createPlan = $this->planApiUseCase->updatePlan($validatedData);
            return ApiResponse::sendResponse(200, __('app.api.plan-updated-successfully'), $createPlan);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $e->getMessage());
        }
    }

    public function allPlans()
    {
        try {
            $plans = $this->planApiUseCase->plans();
            return ApiResponse::sendResponse(200, __('app.api.plans-retrieved-successfully'), $plans);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $e->getMessage());
        }
    }

    public function show(Request $request)
    {
        $validator = Validator::make(
            ['plan_slug' => $request->plan_slug],
            [
                'plan_slug' => ['required', 'exists:plans,slug', new CheckIfPlanBelongsToUserOrAdmin()],
            ],
            [
                'plan_slug.exists' => __('validation.api.plan-slug-invalid'),
                'plan_slug.required' => __('validation.api.plan-slug-does-not-exists')
            ]
        );

        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $errors);
        }
        try {
            $plan = $this->planApiUseCase->show($request->plan_slug);
            return ApiResponse::sendResponse(200, __('app.api.the-plan-retrieved-successfully'), $plan);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $e->getMessage());
        }
    }

    public function destroy(Request $request)
    {
        $slug = $request->plan_slug;
        $validator = Validator::make(
            ['plan_slug' => $slug],
            [
                'plan_slug' => ['bail', 'required', 'exists:plans,slug', new CheckIfPlanBelongsToUser()],
            ],
            [
                'plan_slug.exists' => __('validation.api.plan-slug-invalid'),
                'plan_slug.required' => __('validation.api.plan-slug-does-not-exists')
            ]
        );

        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $errors);
        }

        try {
            $createTrip = $this->planApiUseCase->deletePlan($slug);
            return ApiResponse::sendResponse(200, __('app.api.plan-deleted-successfully'), $createTrip);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $e->getMessage());
        }
    }

    public function myPlans()
    {
        try {
            $plans = $this->planApiUseCase->myPlans();
            return ApiResponse::sendResponse(200, __('app.api.plans-retrieved-successfully'), $plans);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $e->getMessage());
        }
    }

    public function createFavoritePlan(Request $request)
    {
        $id = $request->plan_id;

        $validator = Validator::make(
            ['plan_id' => $id],
            [
                'plan_id' => ['required', 'exists:plans,id', new CheckIfExistsInFavoratblesRule('App\Models\Plan'), new CheckIfPlanBelongsToUserOrAdmin()],
            ],
            [
                'plan_id.exists' => __('validation.api.plan-id-invalid'),
                'plan_id.required' => __('validation.api.plan-id-does-not-exists')
            ]
        );

        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $errors);
        }

        try {
            $createFavPlace = $this->planApiUseCase->createFavoritePlan($id);

            return ApiResponse::sendResponse(200, __('app.api.favorite-plan-created-successfully'), $createFavPlace);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $e->getMessage());
        }
    }

    public  function deleteFavoritePlan(Request $request)
    {
        $id = $request->plan_id;

        $validator = Validator::make(
            ['plan_id' => $id],
            [
                'plan_id' => ['required', 'exists:plans,id', new CheckIfNotExistsInFavoratblesRule('App\Models\Plan')],
            ],
            [
                'plan_id.exists' => __('validation.api.plan-id-invalid'),
                'plan_id.required' => __('validation.api.plan-id-does-not-exists')
            ]
        );

        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $errors);
        }

        try {
            $deleteFavPlan = $this->planApiUseCase->deleteFavoritePlan($id);
            return ApiResponse::sendResponse(200,  __('app.api.you-remove-plan-from-favorite-list'), $deleteFavPlan);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $e->getMessage());
        }
    }

    public function search(Request $request)
    {
        $query = $request->input('query');
        $validator = Validator::make(['query' => $query], [
            'query' => ['bail','nullable','string','regex:/^[\p{Arabic}a-zA-Z0-9\s\-\_\.@]+$/u','max:255'
//                ,new CheckIfHasInjectionBasedTimeRule()
            ],
        ]);
        if ($validator->fails()) {
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $validator->errors()->messages()['query']);
        }
        $validatedQuery = $validator->validated()['query'];

        try {
            $plan = $this->planApiUseCase->search($validatedQuery);
            return ApiResponse::sendResponse(200, __('app.api.the-searched-plan-retrieved-successfully'), $plan);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $e->getMessage());
        }
    }

    public function filter(FilterPlanRequest $request)
    {
        try {
            $plans = $this->planApiUseCase->filter($request->validated());
            return ApiResponse::sendResponse(200, __('app.api.the-searched-plan-retrieved-successfully'), $plans);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $e->getMessage());
        }
    }
}

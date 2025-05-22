<?php

namespace App\Http\Controllers\Api\User;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Rules\CheckIfExistsInFavoratblesRule;
use App\Rules\CheckIfNotExistsInFavoratblesRule;
use App\Rules\CheckIfUserTypeActiveRule;
use App\Rules\CheckLatLngRule;
use App\UseCases\Api\User\FavoriteApiUseCase;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class FavoriteApiController extends Controller
{
    public function __construct(protected FavoriteApiUseCase $favoriteApiUseCase)
    {
        $this->favoriteApiUseCase = $favoriteApiUseCase;
    }

    public function favorite(Request $request, $type, $slug)
    {
        $validator = Validator::make(
            [
                'type' => $type,
                'slug' => $slug
            ],
            [
                'type' => ['bail', 'required', Rule::in(['place', 'trip', 'event', 'volunteering', 'plan', 'guideTrip'])],
                'slug' => ['bail', 'required', new CheckIfExistsInFavoratblesRule(), new CheckIfUserTypeActiveRule()],
            ],
            [
                'slug.required' => __('validation.api.favorite-id-does-not-exists'),
                'type.in' => __('validation.api.not-acceptable-type'),
            ]
        );

        if ($validator->fails()) {
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $validator->errors()->messages());
        }

        try {
            $data = $validator->validated();
            $createFavPlace = $this->favoriteApiUseCase->createFavorite($data);

            return ApiResponse::sendResponse(200,  __('app.api.you-put-this-place-in-favorite-list'), $createFavPlace);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $e->getMessage());
        }
    }

    public function unfavored(Request $request, $type, $slug)
    {
        $validator = Validator::make(
            [
                'type' => $type,
                'slug' => $slug
            ],
            [
                'type' => ['bail', 'required', Rule::in(['place', 'trip', 'event', 'volunteering', 'plan', 'guideTrip'])],
                'slug' => [
                    'required',
                    new CheckIfNotExistsInFavoratblesRule()
                ],
            ],
            [
                'slug.required' => __('validation.api.favorite-id-does-not-exists'),
                'type.in' => __('validation.api.not-acceptable-type'),
            ]
        );

        if ($validator->fails()) {
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $validator->errors()->messages()['slug'][0]);
        }

        try {
            $data = $validator->validated();
            $createFavPlace = $this->favoriteApiUseCase->unfavored($data);

            return ApiResponse::sendResponse(200,  __('app.api.you-delete-this-from-favorite-list'), $createFavPlace);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $e->getMessage());
        }
    }

    public function allUserFavorites()
    {
        try {
            $userFav = $this->favoriteApiUseCase->allUserFavorite();
            return ApiResponse::sendResponse(200,  __('app.api.your-all-favorite-retrieved-successfully'), $userFav);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $e->getMessage());
        }
    }

    public function favSearch(Request $request)
    {
        $query = $request->input('query');
        $lat = request()->lat;
        $lng = request()->lng;
        $validator = Validator::make(
            ['query' => $query, 'lat' => $lat, 'lng' => $lng],
            [
                'query' => 'nullable|string|max:255|regex:/^[\p{Arabic}a-zA-Z0-9\s\-\_\.@]+$/u',
                'lat'   => [
                    'bail',
                    'nullable',
//                    'regex:/^-?\d{1,3}(\.\d{1,6})?$/',   // up to 6 decimal places
//                    'numeric',
                    'between:-90,90',
                    new CheckLatLngRule()
                ],
                'lng'   => [
                    'bail',
                    'nullable',
//                    'regex:/^-?\d{1,3}(\.\d{1,6})?$/',  // up to 6 decimal places
//                    'numeric',
                    'between:-180,180',
                    new CheckLatLngRule()
                ],
            ]
        );
        if ($validator->fails()) {
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $validator->errors()->messages());
        }
        $validated = $validator->validated();
        $validatedQuery = $validated['query'] !== null ? cleanQuery($validated['query']) : null;
        $data = array_merge($validated, ['query' => $validatedQuery]);
        try {
            $users = $this->favoriteApiUseCase->favSearch($data);

            return ApiResponse::sendResponse(200, __('app.api.the-searched-favorite-retrieved-successfully'), $users);
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $e->getMessage());
        }
    }
}

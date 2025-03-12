<?php

namespace App\Http\Controllers\Api\User;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Rules\CheckIfExistsInFavoratblesRule;
use App\Rules\CheckIfNotExistsInFavoratblesRule;
use App\UseCases\Api\User\FavoriteApiUseCase;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class FavoriteApiController extends Controller
{
    protected $favoriteApiUseCase;

    public function __construct(FavoriteApiUseCase $favoriteApiUseCase)
    {

        $this->favoriteApiUseCase = $favoriteApiUseCase;
    }


    public function favorite(Request $request,$type,$slug)
    {
        $validator = Validator::make(
            [
                'type'=>$type,
                'slug' => $slug
            ],
            [
                'type'=>['bail','required',Rule::in(['place', 'trip','event','volunteering','plan','guideTrip'])],
                'slug' => ['required', new CheckIfExistsInFavoratblesRule()],
        ],[
            'slug.required'=>__('validation.api.id-does-not-exists'),
        ]);

        if ($validator->fails()) {
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $validator->errors()->messages());
        }

        try {
            $data= $validator->validated();
            $createFavPlace = $this->favoriteApiUseCase->createFavorite($data);

            return ApiResponse::sendResponse(200,  __('app.place.api.you-put-this-place-in-favorite-list'), $createFavPlace);
        } catch (\Exception $e) {
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $e->getMessage());
        }
    }

    public function unfavored(Request $request,$type,$slug)
    {
        $validator = Validator::make(
            [
                'type'=>$type,
                'slug' => $slug
            ],
            [
                'type'=>['bail','required',Rule::in(['place', 'trip','event','volunteering','plan','guideTrip'])],
                'slug' => ['required', new CheckIfNotExistsInFavoratblesRule()
                ],
            ],[
            'slug.required'=>__('validation.api.id-does-not-exists'),
                'type.in'=>__('validation.api.not-acceptable-type'),
        ]);

        if ($validator->fails()) {
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $validator->errors()->messages()['slug'][0]);
        }

        try {
            $data= $validator->validated();
            $createFavPlace = $this->favoriteApiUseCase->unfavored($data);

            return ApiResponse::sendResponse(200,  __('app.place.api.you-delete-this-from-favorite-list'), $createFavPlace);
        } catch (\Exception $e) {
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $e->getMessage());
        }
    }

    public function allUserFavorites()
    {
        try {
            $userFav = $this->favoriteApiUseCase->allUserFavorite();
            return ApiResponse::sendResponse(200,  __('app.api.your-all-favorite-retrieved-successfully'), $userFav);
        } catch (\Exception $e) {
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $e->getMessage());
        }

    }

    public function favSearch(Request $request)
    {
        $query = $request->input('query');
        try {
            $users = $this->favoriteApiUseCase->favSearch($query);

            return ApiResponse::sendResponse(200, __('app.api.the-searched-favorite-retrieved-successfully'), $users);
        } catch (\Exception $e) {
            return ApiResponse::sendResponseError(Response::HTTP_BAD_REQUEST,  $e->getMessage());
        }
    }
}

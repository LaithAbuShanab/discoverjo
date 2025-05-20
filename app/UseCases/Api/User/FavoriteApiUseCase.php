<?php

namespace App\UseCases\Api\User;

use App\Interfaces\Gateways\Api\User\FavoriteApiRepositoryInterface;
use Illuminate\Support\Facades\Auth;

class FavoriteApiUseCase
{
    protected $favoriteApiRepository;

    public function __construct(FavoriteApiRepositoryInterface $favoriteApiRepository)
    {
        $this->favoriteApiRepository = $favoriteApiRepository;
    }

    public function createFavorite($data)
    {
        $modelClass = 'App\Models\\' . ucfirst($data['type']);
        $typId = $modelClass::findBySlug($data['slug'])?->id;

        $user_id = Auth::guard('api')->user()->id;
        $data = [
            'type'=>$data['type'],
            'type_id' => $typId,
            'user_id' => $user_id
        ];
        return $this->favoriteApiRepository->createFavorite($data);
    }

    public function unfavored($data)
    {
        $modelClass = 'App\Models\\' . ucfirst($data['type']);
        $typId = $modelClass::findBySlug($data['slug'])?->id;

        $user_id = Auth::guard('api')->user()->id;
        $data = [
            'type'=>$data['type'],
            'type_id' => $typId,
            'user_id' => $user_id
        ];
        return $this->favoriteApiRepository->unfavored($data);
    }

    public function allUserFavorite()
    {
        return $this->favoriteApiRepository->allUserFavorite();
    }

    public function favSearch($data){
        return $this->favoriteApiRepository->favSearch($data);
    }
}

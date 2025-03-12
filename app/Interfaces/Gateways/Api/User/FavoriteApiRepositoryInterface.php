<?php

namespace App\Interfaces\Gateways\Api\User;


interface FavoriteApiRepositoryInterface
{
    public function createFavorite($data);
    public function unfavored($data);
    public function allUserFavorite();
    public function favSearch($query);

}

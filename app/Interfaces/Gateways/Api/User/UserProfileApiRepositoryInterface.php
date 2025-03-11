<?php

namespace App\Interfaces\Gateways\Api\User;


interface UserProfileApiRepositoryInterface
{
    public function getUserDetails();
    public function updateProfile($request,$tags,$userImage);
    public function setLocation($request);
    public function PlacesCurrentLocation($request);
    public function allFavorite();
    public function search($query);
    public function favSearch($query);
    public function allTags();
    public function otherUserDetails($slug);



}

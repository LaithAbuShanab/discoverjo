<?php

namespace App\UseCases\Api\User;


use App\Interfaces\Gateways\Api\User\UserProfileApiRepositoryInterface;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class UserProfileApiUseCase
{
    protected $userProfileRepository;

    public function __construct(UserProfileApiRepositoryInterface $userProfileRepository)
    {
        $this->userProfileRepository = $userProfileRepository;
    }

    public function allUserDetails()
    {
        return $this->userProfileRepository->getUserDetails();
    }

    public function updateProfile($request)
    {
        /** @var User $user */
        $user = Auth::guard('api')->user();
        return $this->userProfileRepository->updateProfile([
            'first_name'=>$request['first_name']??$user->first_name,
            'last_name'=>$request['last_name']??$user->last_name,
            'username'=>$request['username']??$user->username,
            'birthday'=>$request['birthday'],
            'sex'=>$request['gender'],
            'description'=>$request['description']??$user->description,
            'phone_number'=>$request['phone_number']??$user->phone_number,
            'status'=>'1'
        ],array_map('trim', explode(',', $request['tags'])),isset($request['image']) ? $request['image'] : null,);
    }

    public function setLocation($request)
    {
        return $this->userProfileRepository->setLocation($request);
    }

    public function PlacesCurrentLocation($request)
    {
        return $this->userProfileRepository->PlacesCurrentLocation($request);
    }

    public function allFavorite()
    {
        return $this->userProfileRepository->allFavorite();
    }

    public function search($query){
        return $this->userProfileRepository->search($query);
    }

    public function favSearch($query){
        return $this->userProfileRepository->favSearch($query);
    }

    public function allTags()
    {
        return $this->userProfileRepository->allTags();

    }

    public function otherUserDetails($slug)
    {
        return $this->userProfileRepository->otherUserDetails($slug);
    }

    public function allNotifications()
    {
        return $this->userProfileRepository->allNotifications();
    }

    public function readNotification($id)
    {
        return $this->userProfileRepository->readNotification($id);
    }

    public function unreadNotifications()
    {
        return $this->userProfileRepository->unreadNotifications();
    }

    public function deleteNotifications($id)
    {
        return $this->userProfileRepository->deleteNotifications($id);
    }
}

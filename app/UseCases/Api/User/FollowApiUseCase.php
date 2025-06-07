<?php

namespace App\UseCases\Api\User;


use App\Interfaces\Gateways\Api\User\FollowApiRepositoryInterface;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class FollowApiUseCase
{
    protected $followRepository;

    public function __construct(FollowApiRepositoryInterface $followRepository)
    {
        $this->followRepository = $followRepository;
    }

    public function follow($slug)
    {
        $following = User::findBySlug($slug);
        $id = Auth::guard('api')->user()->id;
        return $this->followRepository->follow([
            'follower_id'=>$id,
            'following_id'=>$following->id,
            'status'=>0

        ]);
    }

    public function unfollow($following_slug)
    {
        return $this->followRepository->unfollow($following_slug);
    }

    public function acceptFollower($follower_slug){
        return $this->followRepository->acceptFollower($follower_slug);
    }
    public function unacceptedFollower($follower_slug){
        return $this->followRepository->unacceptedFollower($follower_slug);
    }

    public function followersRequest(){
        return $this->followRepository->followersRequest();
    }
    public function followers($user_slug)
    {
        return $this->followRepository->followers($user_slug);
    }
    public function followings($user_slug)
    {
        return $this->followRepository->followings($user_slug);
    }

    public function removeFollower($user_slug)
    {
        return $this->followRepository->removeFollower($user_slug);
    }



}

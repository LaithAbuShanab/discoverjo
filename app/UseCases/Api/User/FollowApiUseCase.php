<?php

namespace App\UseCases\Api\User;


use App\Interfaces\Gateways\Api\User\FollowApiRepositoryInterface;
use Illuminate\Support\Facades\Auth;

class FollowApiUseCase
{
    protected $followRepository;

    public function __construct(FollowApiRepositoryInterface $followRepository)
    {
        $this->followRepository = $followRepository;
    }

    public function follow($request)
    {
        $id = Auth::guard('api')->user()->id;
        return $this->followRepository->follow([
            'follower_id'=>$id,
            'following_id'=>$request->following_id,
            'status'=>0

        ]);
    }

    public function unfollow($following_id)
    {
        return $this->followRepository->unfollow($following_id);
    }

    public function acceptFollower($follower_id){
        return $this->followRepository->acceptFollower($follower_id);
    }
    public function unacceptedFollower($follower_id){
        return $this->followRepository->unacceptedFollower($follower_id);
    }

    public function followersRequest($id){
        return $this->followRepository->followersRequest($id);
    }
    public function followers($user_id)
    {
        return $this->followRepository->followers($user_id);
    }
    public function followings($user_id)
    {
        return $this->followRepository->followings($user_id);
    }


}

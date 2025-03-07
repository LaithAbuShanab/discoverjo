<?php

namespace App\Interfaces\Gateways\Api\User;


interface FollowApiRepositoryInterface
{
    public function follow($request);
    public function unfollow($following_id);
    public function acceptFollower($follower_id);
    public function unacceptedFollower($follower_id);
    public function followersRequest($id);
    public function followers($user_id);
    public function followings($user_id);



}

<?php

namespace App\Interfaces\Gateways\Api\User;


interface FollowApiRepositoryInterface
{
    public function follow($request);
    public function unfollow($following_slug);
    public function acceptFollower($follower_slug);
    public function unacceptedFollower($follower_slug);
    public function followersRequest();
    public function followers($user_slug);
    public function followings($user_slug);



}

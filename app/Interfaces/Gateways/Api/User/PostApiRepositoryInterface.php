<?php

namespace App\Interfaces\Gateways\Api\User;


interface PostApiRepositoryInterface
{
    public function followingPost();
    public function createPost($validatedData,$media);
    public function updatePost($validatedData,$media);
    public function delete($id);
    public function deleteImage($id);
    public function showPost($id);
    public function favorite($id);
    public function deleteFavorite($id);
    public function postLike($data);
}

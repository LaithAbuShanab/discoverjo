<?php

namespace App\Interfaces\Gateways\Api\User;


interface PlaceApiRepositoryInterface
{
    public function singlePlace($id);

    public function createFavoritePlace($data);

    public function deleteFavoritePlace($id);

    public function createVisitedPlace($data);

    public function deleteVisitedPlace($id);

    public function addReview($data);

    public function updateReview($data);

    public function deleteReview($id);



    public function reviewsLike($data);


    public function search($query);
    public function allSearch($query);
    public function filter($data);


}

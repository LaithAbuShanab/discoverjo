<?php

namespace App\Interfaces\Gateways\Api\User;


interface PlaceApiRepositoryInterface
{
    public function singlePlace($slug);

    public function createVisitedPlace($slug);

    public function deleteVisitedPlace($slug);

    public function updateReview($data);

    public function deleteReview($id);

    public function reviewsLike($data);

    public function search($query);

    public function allSearch($query);

    public function filter($data);


}

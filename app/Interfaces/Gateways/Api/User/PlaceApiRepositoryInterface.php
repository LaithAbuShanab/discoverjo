<?php

namespace App\Interfaces\Gateways\Api\User;


interface PlaceApiRepositoryInterface
{
    public function singlePlace($slug);

    public function createVisitedPlace($slug);

    public function deleteVisitedPlace($slug);

    public function search($data);

    public function allSearch($query);

    public function filter($data);


}

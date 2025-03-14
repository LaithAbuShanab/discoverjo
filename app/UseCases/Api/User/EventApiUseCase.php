<?php

namespace App\UseCases\Api\User;


use App\Interfaces\Gateways\Api\User\CategoryApiRepositoryInterface;
use App\Interfaces\Gateways\Api\User\EventApiRepositoryInterface;
use Illuminate\Support\Facades\Auth;

class EventApiUseCase
{
    protected $eventRepository;

    public function __construct(EventApiRepositoryInterface $eventRepository)
    {
        $this->eventRepository = $eventRepository;
    }

    public function allEvents()
    {
        return $this->eventRepository->getAllEvents();
    }

    public function activeEvents()
    {
        return $this->eventRepository->activeEvents();
    }

    public function event($slug)
    {
        return $this->eventRepository->event($slug);
    }

    public function dateEvents($date)
    {
        return $this->eventRepository->dateEvents($date);
    }

    public function interestEvent($slug)
    {
        return $this->eventRepository->createInterestEvent($slug);
    }

    public function disinterestEvent($slug)
    {

        return $this->eventRepository->disinterestEvent($slug);
    }

    public function favorite($id)
    {
        return $this->eventRepository->favorite($id);
    }

    public function deleteFavorite($id)
    {
        return $this->eventRepository->deleteFavorite($id);
    }

    public function addReview($data)
    {
        return $this->eventRepository->addReview($data);
    }

    public function updateReview($data)
    {
        return $this->eventRepository->updateReview($data);
    }

    public function deleteReview($id)
    {
        return $this->eventRepository->deleteReview($id);
    }

    public function reviewsLike($data)
    {
        return $this->eventRepository->reviewsLike($data);
    }

    public function search($query)
    {
        return $this->eventRepository->search($query);
    }

    public function interestList($id)
    {

        return $this->eventRepository->interestList($id);
    }
}

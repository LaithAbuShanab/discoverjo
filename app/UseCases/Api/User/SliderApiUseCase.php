<?php

namespace App\UseCases\Api\User;


use App\Interfaces\Gateways\Api\User\SliderApiRepositoryInterface;

class SliderApiUseCase
{
    protected $sliderRepository;

    public function __construct(SliderApiRepositoryInterface $sliderRepository)
    {
        $this->sliderRepository = $sliderRepository;
    }

    public function allOnboardings()
    {
        return $this->sliderRepository->allOnboardings();
    }




}

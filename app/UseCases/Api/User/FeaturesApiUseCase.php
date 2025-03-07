<?php

namespace App\UseCases\Api\User;

use App\Interfaces\Gateways\Api\User\FeaturesApiRepositoryInterface;
use Illuminate\Support\Facades\Auth;

class FeaturesApiUseCase
{
    protected $featuresRepository;

    public function __construct(FeaturesApiRepositoryInterface $featuresRepository)
    {
        $this->featuresRepository = $featuresRepository;
    }

    public function allFeatures()
    {
        return $this->featuresRepository->allFeatures();
    }
}

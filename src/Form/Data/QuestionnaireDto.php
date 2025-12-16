<?php

declare(strict_types=1);

namespace App\Form\Data;

use App\Form\Data\Step\ContactDto;
use App\Form\Data\Step\ReponsesDispositionDto;
use App\Form\Data\Step\ReponsesScoreDto;
use Symfony\Component\Validator\Constraints as Assert;

final class QuestionnaireDto
{
    public string $currentStep = 'contact';

    #[Assert\Valid(groups: ['contact'])]
    public ?ContactDto $contact = null;

    #[Assert\Valid(groups: ['reponsesScore'])]
    public ?ReponsesScoreDto $reponsesScore = null;

    #[Assert\Valid(groups: ['reponsesDisposition'])]
    public ?ReponsesDispositionDto $reponsesDisposition = null;
}

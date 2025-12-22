<?php

declare(strict_types=1);

namespace App\Form\Dto;

use App\Form\Dto\Step\Contact;
use App\Form\Dto\Step\ReponsesDisposition;
use App\Form\Dto\Step\ReponsesScore;
use Symfony\Component\Validator\Constraints as Assert;

final class Questionnaire
{
    public string $currentStep = 'contact';

    #[Assert\Valid(groups: ['contact'])]
    public ?Contact $contact = null;

    #[Assert\Valid(groups: ['reponsesScore'])]
    public ?ReponsesScore $reponsesScore = null;

    #[Assert\Valid(groups: ['reponsesDisposition'])]
    public ?ReponsesDisposition $reponsesDisposition = null;
}

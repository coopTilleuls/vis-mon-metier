<?php

declare(strict_types=1);

namespace App\Form\Data\Step;

use Symfony\Component\Validator\Constraints as Assert;

final class ReponsesDispositionDto
{
    /**
     * @var array<mixed>|null
     */
    #[Assert\All([
        new Assert\NotBlank(),
    ], groups: ['reponsesDisposition'])]
    public ?array $reponses = null;
}

<?php

declare(strict_types=1);

namespace App\Form\Data\Step;

use Symfony\Component\Validator\Constraints as Assert;

final class ReponsesScoreDto
{
    /**
     * @var array<mixed>|null
     */
    #[Assert\All([
        new Assert\NotBlank(),
    ], groups: ['reponsesScore'])]
    public ?array $reponses = null;
}

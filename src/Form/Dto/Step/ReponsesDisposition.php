<?php

declare(strict_types=1);

namespace App\Form\Dto\Step;

use Symfony\Component\Validator\Constraints as Assert;

final class ReponsesDisposition
{
    /**
     * @var array<mixed>|null
     */
    #[Assert\All([
        new Assert\NotBlank(),
    ], groups: ['reponsesDisposition'])]
    public ?array $reponses = null;
}

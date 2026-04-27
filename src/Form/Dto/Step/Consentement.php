<?php

declare(strict_types=1);

namespace App\Form\Dto\Step;

use Symfony\Component\Validator\Constraints as Assert;

final class Consentement
{
    #[Assert\IsTrue(
        message: 'Pour valider votre inscription, il est nécessaire d\'accepter les modalités de traitement de vos données personnelles.',
        groups: ['consentement']
    )]
    public bool $consentementRgpd = false;
}

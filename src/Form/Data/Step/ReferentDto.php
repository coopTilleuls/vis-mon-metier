<?php

declare(strict_types=1);

namespace App\Form\Data\Step;

use App\Entity\Referent;
use Symfony\Component\ObjectMapper\Attribute\Map;
use Symfony\Component\Validator\Constraints as Assert;

#[Map(target: Referent::class)]
final class ReferentDto
{
    #[Assert\Email(groups: ['contact'])]
    public ?string $email = null;

    #[Assert\Length(max: 255, groups: ['contact'])]
    public ?string $prenom = null;

    #[Assert\Length(max: 255, groups: ['contact'])]
    public ?string $nomFamille = null;

    #[Assert\Length(max: 255, groups: ['contact'])]
    public ?string $telephone = null;

    #[Assert\Length(max: 255, groups: ['contact'])]
    public ?string $fonction = null;
}

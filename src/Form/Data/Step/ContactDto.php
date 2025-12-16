<?php

declare(strict_types=1);

namespace App\Form\Data\Step;

use App\Entity\Contact;
use Symfony\Component\Validator\Constraints as Assert;

final class ContactDto
{
    #[Assert\Length(max: 255, groups: ['contact'])]
    public ?string $nomEntreprise = null;

    #[Assert\Length(max: 255, groups: ['contact'])]
    public ?string $secteurActivite = null;

    #[Assert\Length(max: 255, groups: ['contact'])]
    public ?string $type = null;

    #[Assert\Length(max: 50, groups: ['contact'])]
    public ?string $nombreEmployes = null;

    #[Assert\NotBlank(groups: ['contact'])]
    #[Assert\Length(max: 255, groups: ['contact'])]
    public ?string $prenom = null;

    #[Assert\NotBlank(groups: ['contact'])]
    #[Assert\Length(max: 255, groups: ['contact'])]
    public ?string $nomFamille = null;

    #[Assert\Length(max: 255, groups: ['contact'])]
    public ?string $metier = null;

    #[Assert\Length(min: 10, max: 10, groups: ['contact'])]
    public ?string $numeroTelephone = null;

    #[Assert\Valid(groups: ['contact'])]
    public ?ReferentDto $referent = null;

    #[Assert\Choice(
        choices: [
            Contact::SALARIE,
            Contact::DEMANDEUR_EMPLOI,
            Contact::SCOLAIRE_ETUDIANT,
            Contact::AUTRE,
        ],
        groups: ['contact']
    )]
    public ?string $situation = null;

    #[Assert\Choice(
        choices: [
            Contact::MAJEUR,
            Contact::MINEUR,
        ],
        groups: ['contact']
    )]
    public ?string $trancheAge = null;

    #[Assert\NotBlank(groups: ['contact'])]
    #[Assert\Email(groups: ['contact'])]
    #[Assert\Length(max: 255, groups: ['contact'])]
    public ?string $email = null;

    #[Assert\NotBlank(groups: ['contact'])]
    #[Assert\Length(max: 255, groups: ['contact'])]
    public ?string $commune = null;

    public function __construct()
    {
        $this->referent = new ReferentDto();
    }
}

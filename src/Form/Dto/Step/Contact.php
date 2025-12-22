<?php

declare(strict_types=1);

namespace App\Form\Dto\Step;

use App\Entity\Contact as ContactEntity;
use Symfony\Component\Validator\Constraints as Assert;

final class Contact
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

    #[Assert\Choice(
        choices: [
            ContactEntity::SALARIE,
            ContactEntity::DEMANDEUR_EMPLOI,
            ContactEntity::SCOLAIRE_ETUDIANT,
            ContactEntity::AUTRE,
        ],
        groups: ['contact']
    )]
    public ?string $situation = null;

    #[Assert\Choice(
        choices: [
            ContactEntity::MAJEUR,
            ContactEntity::MINEUR,
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

    #[Assert\Length(max: 255, groups: ['contact'])]
    public ?string $prenomTuteur = null;

    #[Assert\Length(max: 255, groups: ['contact'])]
    public ?string $nomFamilleTuteur = null;

    #[Assert\Length(max: 255, groups: ['contact'])]
    public ?string $nomReferent = null;

    #[Assert\Length(max: 255, groups: ['contact'])]
    public ?string $prenomReferent = null;

    #[Assert\Length(max: 255, groups: ['contact'])]
    public ?string $structureReferent = null;
}

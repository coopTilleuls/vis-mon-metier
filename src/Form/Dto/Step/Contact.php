<?php

declare(strict_types=1);

namespace App\Form\Dto\Step;

use App\Entity\Contact as ContactEntity;
use App\Entity\Referent;
use Symfony\Component\Validator\Constraints as Assert;

final class Contact
{
    public ?string $type = null;

    #[Assert\NotBlank(message: 'Veuillez remplir ce champ.', groups: ['contact'])]
    #[Assert\Length(min: 2, minMessage: 'Le prénom doit contenir au moins 2 caractères.', groups: ['contact'])]
    public ?string $prenom = null;

    #[Assert\NotBlank(message: 'Veuillez remplir ce champ.', groups: ['contact'])]
    #[Assert\Length(min: 2, minMessage: 'Le nom de famille doit contenir au moins 2 caractères.', groups: ['contact'])]
    public ?string $nomFamille = null;

    #[Assert\When(
        expression: "this.type == 'employeur'",
        constraints: [
            new Assert\NotBlank(message: 'Veuillez remplir ce champ.'),
        ],
        groups: ['contact']
    )]
    public ?string $fonctionSignataire = null;

    #[Assert\NotBlank(message: 'Veuillez remplir ce champ.', groups: ['contact'])]
    #[Assert\Email(message: 'L\'adresse email n\'est pas valide.', groups: ['contact'])]
    public ?string $email = null;

    #[Assert\NotBlank(groups: ['contact'])]
    #[Assert\Regex(pattern: '/^(?:(?:\+|00)33|0)\s*[1-9](?:[\s.-]*\d{2}){4}$/', message: 'Le numéro de téléphone n\'est pas valide (format français attendu).', groups: ['contact'])]
    public ?string $numeroTelephone = null;

    #[Assert\NotBlank(message: 'Veuillez remplir ce champ.', groups: ['contact'])]
    public ?string $commune = null;

    #[Assert\When(
        expression: "this.type == 'participant' && this.commune == 'Autre'",
        constraints: [
            new Assert\NotBlank(message: 'Veuillez préciser votre commune.'),
        ],
        groups: ['contact']
    )]
    public ?string $communeAutre = null;

    #[Assert\When(
        expression: "this.type == 'participant'",
        constraints: [
            new Assert\NotBlank(message: 'Veuillez remplir ce champ.'),
            new Assert\Choice(choices: [
                ContactEntity::SCOLAIRE_ETUDIANT,
                ContactEntity::DEMANDEUR_EMPLOI,
                ContactEntity::SALARIE,
                ContactEntity::AUTRE,
            ],
                message: 'Veuillez choisir une situation valide.'),
        ],
        groups: ['contact']
    )]
    public ?string $situation = null;

    #[Assert\When(
        expression: "this.type == 'participant'",
        constraints: [
            new Assert\NotBlank(message: 'Veuillez remplir ce champ.'),
            new Assert\Choice(
                choices: [
                    ContactEntity::MAJEUR,
                    ContactEntity::MINEUR,
                ],
                message: 'Veuillez choisir une tranche d\'âge valide.'
            ),
        ],
        groups: ['contact']
    )]
    public ?string $trancheAge = null;

    #[Assert\When(
        expression: "this.type == 'participant' && this.situation == 'Scolaire, Etudiant·e'",
        constraints: [
            new Assert\NotNull(message: 'Veuillez sélectionner un·e référent·e (obligatoire pour les scolaires et étudiant·e·s).'),
        ],
        groups: ['contact']
    )]
    #[Assert\Type(Referent::class, message: 'Le référent n\'est pas valide.', groups: ['contact'])]
    public ?Referent $referent = null;

    #[Assert\When(
        expression: "this.type == 'employeur'",
        constraints: [
            new Assert\NotBlank(message: 'Veuillez remplir ce champ.'),
        ],
        groups: ['contact']
    )]
    public ?string $nomEntreprise = null;

    #[Assert\When(
        expression: "this.type == 'employeur'",
        constraints: [
            new Assert\NotBlank(message: 'Veuillez remplir ce champ.'),
        ],
        groups: ['contact']
    )]
    public ?string $secteurActivite = null;

    #[Assert\When(
        expression: "this.type == 'employeur'",
        constraints: [
            new Assert\NotBlank(message: 'Veuillez remplir ce champ.'),
            new Assert\Choice(choices: [
                '- de 10 salarié·e·s',
                'Entre 11 et 50 salarié·e·s',
                'Entre 51 et 250 salarié·e·s',
                '+ de 250 salarié·e·s',
            ], message: 'Veuillez choisir une valeur valide.'),
        ],
        groups: ['contact']
    )]
    public ?string $nombreEmployes = null;

    #[Assert\When(
        expression: "this.type == 'employeur'",
        constraints: [
            new Assert\NotBlank(message: 'Veuillez remplir ce champ.'),
        ],
        groups: ['contact']
    )]
    public ?string $adresseEntreprise = null;

    #[Assert\When(
        expression: "this.type == 'employeur'",
        constraints: [
            new Assert\NotBlank(message: 'Veuillez remplir ce champ.'),
        ],
        groups: ['contact']
    )]
    public ?string $metier = null;

    #[Assert\When(
        expression: "this.type == 'employeur'",
        constraints: [
            new Assert\NotNull(message: 'Ce champ est obligatoire.'),
            new Assert\Positive(message: 'Le nombre d\'immersions doit être supérieur à 0.'),
        ],
        groups: ['contact']
    )]
    public ?int $nombreImmersion = null;

    #[Assert\When(
        expression: "this.type == 'employeur'",
        constraints: [
            new Assert\NotBlank(message: 'Veuillez répondre à cette question.'),
            new Assert\Choice(choices: ['1', '0'], message: 'Valeur invalide.'),
        ],
        groups: ['contact']
    )]
    public ?string $immersionMemeDate = null;

    #[Assert\When(
        expression: "this.type == 'employeur'",
        constraints: [
            new Assert\NotBlank(message: 'Veuillez remplir ce champ.'),
        ],
        groups: ['contact']
    )]
    public ?string $prenomTuteur = null;

    #[Assert\When(
        expression: "this.type == 'employeur'",
        constraints: [
            new Assert\NotBlank(message: 'Veuillez remplir ce champ.'),
        ],
        groups: ['contact']
    )]
    public ?string $nomFamilleTuteur = null;

    #[Assert\When(
        expression: "this.type == 'employeur'",
        constraints: [
            new Assert\NotBlank(message: 'Veuillez remplir ce champ.'),
        ],
        groups: ['contact']
    )]
    public ?string $fonctionTuteur = null;

    #[Assert\Email(message: 'L\'adresse email n\'est pas valide.', groups: ['contact'])]
    public ?string $emailTuteur = null;

    #[Assert\Regex(pattern: '/^(?:(?:\+|00)33|0)\s*[1-9](?:[\s.-]*\d{2}){4}$/', message: 'Le numéro de téléphone n\'est pas valide (format français attendu).', groups: ['contact'])]
    public ?string $telephoneTuteur = null;

    #[Assert\When(
        expression: "this.type == 'employeur'",
        constraints: [
            new Assert\NotBlank(message: 'Veuillez remplir ce champ.'),
            new Assert\Regex(pattern: '/^\d{14}$/', message: 'Le SIRET doit contenir exactement 14 chiffres.'),
        ],
        groups: ['contact']
    )]
    public ?string $siret = null;

    #[Assert\When(
        expression: "this.type == 'employeur'",
        constraints: [
            new Assert\NotBlank(message: 'Veuillez remplir ce champ.'),
        ],
        groups: ['contact']
    )]
    public ?string $horaires = null;

    #[Assert\When(
        expression: "this.type == 'employeur'",
        constraints: [
            new Assert\NotBlank(message: 'Veuillez répondre à cette question.'),
            new Assert\Choice(choices: ['1', '0'], message: 'Valeur invalide.'),
        ],
        groups: ['contact']
    )]
    public ?string $epiDisponible = null;

    #[Assert\When(
        expression: "this.type == 'employeur'",
        constraints: [
            new Assert\NotBlank(message: 'Veuillez répondre à cette question.'),
            new Assert\Choice(choices: ['1', '0'], message: 'Valeur invalide.'),
        ],
        groups: ['contact']
    )]
    public ?string $autresMesuresPrevention = null;

    #[Assert\When(
        expression: "this.type == 'employeur' && this.autresMesuresPrevention == '1'",
        constraints: [
            new Assert\NotBlank(message: 'Veuillez préciser les autres mesures de prévention.'),
        ],
        groups: ['contact']
    )]
    public ?string $precisionMesuresPrevention = null;
}

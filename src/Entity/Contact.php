<?php

declare(strict_types=1);

namespace App\Entity;

use App\ValueObject\Type;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\ObjectMapper\Attribute\Map;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\Table(name: 'contact')]
class Contact
{
    public const DEMANDEUR_EMPLOI = 'Demandeur d\'emploi';
    public const SCOLAIRE_ETUDIANT = 'Scolaire, Etudiant';
    public const SALARIE = 'Salarié';
    public const AUTRE = 'Autre';
    public const MAJEUR = 'Plus de 18 ans';
    public const MINEUR = 'Moins de 18 ans';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Map(if: false)]
    private int $id;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $nomEntreprise = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $secteurActivite = null;

    #[ORM\Column(length: 255)]
    #[Assert\Choice(choices: [Type::EMPLOYEUR, Type::PARTICIPANT], message: 'Type invalide')]
    private string $type;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $nombreEmployes = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $prenom = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $nomFamille = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $numeroTelephone = null;

    #[ORM\Column(length: 50, nullable: true)]
    #[Assert\Choice(choices: [self::SALARIE, self::DEMANDEUR_EMPLOI, self::SCOLAIRE_ETUDIANT, self::AUTRE], message: 'Type invalide')]
    private ?string $situation = null;

    #[ORM\Column(length: 50, nullable: true)]
    #[Assert\Choice(choices: [self::MAJEUR, self::MINEUR], message: 'Type invalide')]
    private ?string $trancheAge = null;

    #[ORM\Column(length: 255)]
    private string $email;

    #[ORM\Column(length: 255)]
    private string $commune;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $prenomTuteur = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $nomFamilleTuteur = null;

    #[ORM\ManyToOne(targetEntity: Referent::class, cascade: ['persist'])]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    #[Map(if: false)]
    private ?Referent $referent = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $nomReferent = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $prenomReferent = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $structureReferent = null;

    public function getId(): int
    {
        return $this->id;
    }

    public function getNomEntreprise(): ?string
    {
        return $this->nomEntreprise;
    }

    public function setNomEntreprise(?string $nomEntreprise): static
    {
        $this->nomEntreprise = $nomEntreprise;

        return $this;
    }

    public function getSecteurActivite(): ?string
    {
        return $this->secteurActivite;
    }

    public function setSecteurActivite(?string $secteurActivite): static
    {
        $this->secteurActivite = $secteurActivite;

        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }

    public function getNombreEmployes(): ?string
    {
        return $this->nombreEmployes;
    }

    public function setNombreEmployes(?string $nombreEmployes): static
    {
        $this->nombreEmployes = $nombreEmployes;

        return $this;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(?string $prenom): static
    {
        $this->prenom = $prenom;

        return $this;
    }

    public function getNomFamille(): ?string
    {
        return $this->nomFamille;
    }

    public function setNomFamille(?string $nomFamille): static
    {
        $this->nomFamille = $nomFamille;

        return $this;
    }

    public function getNumeroTelephone(): ?string
    {
        return $this->numeroTelephone;
    }

    public function setNumeroTelephone(?string $numeroTelephone): static
    {
        $this->numeroTelephone = $numeroTelephone;

        return $this;
    }

    public function getSituation(): ?string
    {
        return $this->situation;
    }

    public function setSituation(?string $situation): void
    {
        $this->situation = $situation;
    }

    public function getTrancheAge(): ?string
    {
        return $this->trancheAge;
    }

    public function setTrancheAge(?string $trancheAge): static
    {
        $this->trancheAge = $trancheAge;

        return $this;
    }

    public function getReferent(): ?Referent
    {
        return $this->referent;
    }

    public function setReferent(?Referent $referent): static
    {
        $this->referent = $referent;

        return $this;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getCommune(): string
    {
        return $this->commune;
    }

    public function setCommune(string $commune): void
    {
        $this->commune = $commune;
    }

    public function getPrenomTuteur(): ?string
    {
        return $this->prenomTuteur;
    }

    public function setPrenomTuteur(?string $prenomTuteur): void
    {
        $this->prenomTuteur = $prenomTuteur;
    }

    public function getNomFamilleTuteur(): ?string
    {
        return $this->nomFamilleTuteur;
    }

    public function setNomFamilleTuteur(?string $nomFamilleTuteur): void
    {
        $this->nomFamilleTuteur = $nomFamilleTuteur;
    }

    public function getNomReferent(): ?string
    {
        return $this->nomReferent;
    }

    public function setNomReferent(?string $nomReferent): void
    {
        $this->nomReferent = $nomReferent;
    }

    public function getPrenomReferent(): ?string
    {
        return $this->prenomReferent;
    }

    public function setPrenomReferent(?string $prenomReferent): void
    {
        $this->prenomReferent = $prenomReferent;
    }

    public function getStructureReferent(): ?string
    {
        return $this->structureReferent;
    }

    public function setStructureReferent(?string $structureReferent): void
    {
        $this->structureReferent = $structureReferent;
    }

    public function __toString(): string
    {
        return "{$this->getPrenom()} {$this->getNomFamille()}";
    }
}

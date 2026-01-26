<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
class Session
{
    public const STATUT_BROUILLON = 'brouillon';
    public const STATUT_OUVERT = 'ouverte';
    public const STATUT_FERME = 'fermée';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Le nom est obligatoire')]
    private string $nom;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Assert\NotNull(message: 'La date de début est obligatoire')]
    private \DateTimeInterface $dateDebut;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Assert\NotNull(message: 'La date de fin est obligatoire')]
    private \DateTimeInterface $dateFin;

    #[ORM\Column(length: 50)]
    #[Assert\Choice(choices: [self::STATUT_BROUILLON, self::STATUT_OUVERT, self::STATUT_FERME], message: 'Statut invalide')]
    private string $statut = self::STATUT_BROUILLON;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true)]
    private ?ModelQuestionnaire $modelQuestionnaire = null;

    public function getId(): int
    {
        return $this->id;
    }

    public function getNom(): string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    public function getDateDebut(): \DateTimeInterface
    {
        return $this->dateDebut;
    }

    public function setDateDebut(\DateTimeInterface $dateDebut): static
    {
        $this->dateDebut = $dateDebut;

        return $this;
    }

    public function getDateFin(): \DateTimeInterface
    {
        return $this->dateFin;
    }

    public function setDateFin(\DateTimeInterface $dateFin): static
    {
        $this->dateFin = $dateFin;

        return $this;
    }

    public function getStatut(): string
    {
        return $this->statut;
    }

    public function setStatut(string $statut): static
    {
        $this->statut = $statut;

        return $this;
    }

    /**
     * @return string[]
     */
    public static function getStatutChoices(): array
    {
        return [
            self::STATUT_OUVERT => self::STATUT_OUVERT,
            self::STATUT_FERME => self::STATUT_FERME,
            self::STATUT_BROUILLON => self::STATUT_BROUILLON,
        ];
    }

    public function getModelQuestionnaire(): ?ModelQuestionnaire
    {
        return $this->modelQuestionnaire;
    }

    public function setModelQuestionnaire(?ModelQuestionnaire $modelQuestionnaire): static
    {
        $this->modelQuestionnaire = $modelQuestionnaire;

        return $this;
    }

    public function __toString(): string
    {
        return $this->nom;
    }
}

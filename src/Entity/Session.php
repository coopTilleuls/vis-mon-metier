<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
class Session
{
    public const STATUT_BROUILLON = 'brouillon';
    public const STATUT_OUVERT = 'ouvert';
    public const STATUT_FERME = 'ferme';

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

    /**
     * @var array<string, mixed>
     */
    #[ORM\Column(type: 'json')]
    private array $configurationQuestionnaire;

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
     * @return array<string, mixed>
     */
    public function getConfigurationQuestionnaire(): array
    {
        return $this->configurationQuestionnaire;
    }

    /**
     * @param array<string, mixed> $configurationQuestionnaire
     */
    public function setConfigurationQuestionnaire(array $configurationQuestionnaire): static
    {
        $this->configurationQuestionnaire = $configurationQuestionnaire;

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
}

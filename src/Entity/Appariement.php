<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class Appariement
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Questionnaire::class, inversedBy: 'appariementsParticipant')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Questionnaire $participant = null;

    #[ORM\ManyToOne(targetEntity: Questionnaire::class, inversedBy: 'appariementsEntreprise')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Questionnaire $entreprise = null;

    #[ORM\Column(type: 'date_immutable', nullable: true)]
    private ?\DateTimeImmutable $dateMatch = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getParticipant(): ?Questionnaire
    {
        return $this->participant;
    }

    public function setParticipant(?Questionnaire $participant): static
    {
        $this->participant = $participant;
        $participant?->setAppaire(true);

        return $this;
    }

    public function getEntreprise(): ?Questionnaire
    {
        return $this->entreprise;
    }

    public function setEntreprise(?Questionnaire $entreprise): static
    {
        $this->entreprise = $entreprise;
        $entreprise?->setAppaire(true);

        return $this;
    }

    public function getDateMatch(): ?\DateTimeImmutable
    {
        return $this->dateMatch;
    }

    public function setDateMatch(?\DateTimeImmutable $dateMatch): static
    {
        $this->dateMatch = $dateMatch;

        return $this;
    }

    public function __toString(): string
    {
        $participant = $this->getParticipant()?->getContact();
        $entreprise = $this->getEntreprise()?->getContact()?->getNomEntreprise();
        $date = $this->getDateMatch()?->format('d/m/Y');

        return sprintf('%s avec %s le %s', $participant, $entreprise, $date);
    }
}

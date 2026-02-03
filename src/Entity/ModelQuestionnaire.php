<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class ModelQuestionnaire
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    /**
     * @var array<mixed>
     */
    #[ORM\Column(type: 'json')]
    private array $questionsDisposition;

    /**
     * @var array<mixed>
     */
    #[ORM\Column(type: 'json')]
    private array $questionsScore;

    #[ORM\Column]
    private bool $active = true;

    #[ORM\Column]
    private string $nom = 'Questionnaire - Vis mon Métier';

    /**
     * @var string[]
     */
    #[ORM\Column(type: 'json')]
    private array $communes = [];

    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return array<mixed>
     */
    public function getQuestionsDisposition(): array
    {
        return $this->questionsDisposition;
    }

    /**
     * @param array<mixed> $questionsDisposition
     */
    public function setQuestionsDisposition(array $questionsDisposition): void
    {
        $this->questionsDisposition = $questionsDisposition;
    }

    /**
     * @return array<mixed>
     */
    public function getQuestionsScore(): array
    {
        return $this->questionsScore;
    }

    /**
     * @param array<mixed> $questionsScore
     */
    public function setQuestionsScore(array $questionsScore): void
    {
        $this->questionsScore = $questionsScore;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): void
    {
        $this->active = $active;
    }

    public function getNom(): string
    {
        return $this->nom;
    }

    public function setNom(string $nom): void
    {
        $this->nom = $nom;
    }

    /**
     * @return string[]
     */
    public function getCommunes(): array
    {
        return $this->communes;
    }

    /**
     * @param string[] $communes
     */
    public function setCommunes(array $communes): self
    {
        $this->communes = $communes;

        return $this;
    }

    public function __toString(): string
    {
        return $this->nom;
    }
}

<?php

namespace App\Entity;

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
}

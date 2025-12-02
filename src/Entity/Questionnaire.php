<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\HasLifecycleCallbacks]
class Questionnaire
{
    public const TYPE_EMPLOYEUR = 'employeur';
    public const TYPE_PARTICIPANT = 'participant';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    #[ORM\ManyToOne(targetEntity: Session::class)]
    #[ORM\JoinColumn(nullable: false)]
    private Session $session;

    #[ORM\ManyToOne(targetEntity: Contact::class)]
    #[ORM\JoinColumn(nullable: false)]
    private Contact $contact;

    #[ORM\Column(length: 20)]
    #[Assert\Choice(choices: [self::TYPE_EMPLOYEUR, self::TYPE_PARTICIPANT], message: 'Type invalide')]
    private string $type;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Le métier est obligatoire.')]
    private string $metier;

    /**
     * @var array<array{question_id: int, ordre: int, score: int}>
     */
    #[ORM\Column(type: 'json')]
    private array $reponses;

    /**
     * @var array<array{date_debut: \DateTimeImmutable, date_fin: \DateTimeImmutable}>
     */
    #[ORM\Column(type: 'json')]
    private array $plagesDates;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getSession(): Session
    {
        return $this->session;
    }

    public function setSession(Session $session): static
    {
        $this->session = $session;

        return $this;
    }

    public function getContact(): Contact
    {
        return $this->contact;
    }

    public function setContact(Contact $contact): static
    {
        $this->contact = $contact;

        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getMetier(): string
    {
        return $this->metier;
    }

    public function setMetier(string $metier): static
    {
        $this->metier = $metier;

        return $this;
    }

    /**
     * @return array<array{question_id: int, ordre:int, score: int}>
     */
    public function getReponses(): array
    {
        return $this->reponses;
    }

    /**
     * @param array<array{question_id: int, ordre: int, score: int}> $reponses
     */
    public function setReponses(array $reponses): static
    {
        $this->reponses = $reponses;

        return $this;
    }

    /**
     * @return array<array{date_debut: \DateTimeImmutable, date_fin: \DateTimeImmutable}>
     */
    public function getPlagesDates(): array
    {
        return $this->plagesDates;
    }

    /**
     * @param array<array{date_debut: \DateTimeImmutable, date_fin: \DateTimeImmutable}> $plagesDates
     */
    public function setPlagesDates(array $plagesDates): static
    {
        $this->plagesDates = $plagesDates;

        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }
}

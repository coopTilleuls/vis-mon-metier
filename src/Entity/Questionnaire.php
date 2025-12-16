<?php

declare(strict_types=1);

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
    #[ORM\JoinColumn(nullable: true)]
    private ?Contact $contact;

    #[ORM\Column(length: 20)]
    #[Assert\Choice(choices: [self::TYPE_EMPLOYEUR, self::TYPE_PARTICIPANT], message: 'Type invalide')]
    private string $type;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $metier = null;

    /**
     * @var array<mixed>
     */
    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $reponsesDisposition = null;

    /**
     * @var array<mixed>
     */
    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $reponsesScore = null;

    /**
     * @var array<int, \DateTimeImmutable>
     */
    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $dates = null;

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

    public function setSession(Session $session): void
    {
        $this->session = $session;
    }

    public function getContact(): ?Contact
    {
        return $this->contact;
    }

    public function setContact(?Contact $contact): void
    {
        $this->contact = $contact;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }

    public function getMetier(): ?string
    {
        return $this->metier;
    }

    public function setMetier(?string $metier): void
    {
        $this->metier = $metier;
    }

    /**
     * @return array<mixed>|null
     */
    public function getReponsesDisposition(): ?array
    {
        return $this->reponsesDisposition;
    }

    /**
     * @param array<mixed>|null $reponsesDisposition
     */
    public function setReponsesDisposition(?array $reponsesDisposition): void
    {
        $this->reponsesDisposition = $reponsesDisposition;
    }

    /**
     * @return array<mixed>|null
     */
    public function getReponsesScore(): ?array
    {
        return $this->reponsesScore;
    }

    /**
     * @param array<mixed>|null $reponsesScore
     */
    public function setReponsesScore(?array $reponsesScore): void
    {
        $this->reponsesScore = $reponsesScore;
    }

    /**
     * @return array<int, \DateTimeImmutable>|null
     */
    public function getDates(): ?array
    {
        return $this->dates;
    }

    /**
     * @param array<int, \DateTimeImmutable>|null $dates
     */
    public function setDates(?array $dates): void
    {
        $this->dates = $dates;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): void
    {
        $this->createdAt = $createdAt;
    }
}

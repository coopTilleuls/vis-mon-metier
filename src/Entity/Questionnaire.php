<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\QuestionnaireRepository;
use App\ValueObject\Type;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Event\PreRemoveEventArgs;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: QuestionnaireRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Questionnaire
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    #[ORM\ManyToOne(targetEntity: Session::class)]
    #[ORM\JoinColumn(nullable: false)]
    private Session $session;

    #[ORM\ManyToOne(targetEntity: Contact::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: 'CASCADE')]
    private ?Contact $contact;

    #[ORM\Column(length: 20)]
    #[Assert\Choice(choices: [Type::EMPLOYEUR, Type::PARTICIPANT], message: 'Type invalide')]
    private string $type;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $metier = null;

    #[ORM\Column(nullable: false, options: ['default' => 1])]
    private int $nombreImmersion = 1;

    #[ORM\Column(nullable: true)]
    private ?bool $immersionMemeDate = null;

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

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(options: ['default' => false])]
    private bool $appaire = false;

    /**
     * @var Collection<int, Appariement>
     */
    #[ORM\OneToMany(targetEntity: Appariement::class, mappedBy: 'participant', cascade: ['persist', 'remove'])]
    private Collection $appariementsParticipant;

    /**
     * @var Collection<int, Appariement>
     */
    #[ORM\OneToMany(targetEntity: Appariement::class, mappedBy: 'entreprise', cascade: ['persist', 'remove'])]
    private Collection $appariementsEntreprise;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->appariementsParticipant = new ArrayCollection();
        $this->appariementsEntreprise = new ArrayCollection();
    }

    #[ORM\PreRemove]
    public function onPreRemove(PreRemoveEventArgs $event): void
    {
        $contact = $this->getContact();
        if ($contact) {
            $entityManager = $event->getObjectManager();
            $entityManager->remove($contact);
        }
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

    public function getNombreImmersion(): int
    {
        return $this->nombreImmersion;
    }

    public function setNombreImmersion(int $nombreImmersion): void
    {
        $this->nombreImmersion = $nombreImmersion;
    }

    public function getImmersionMemeDate(): ?bool
    {
        return $this->immersionMemeDate;
    }

    public function setImmersionMemeDate(?bool $immersionMemeDate): void
    {
        $this->immersionMemeDate = $immersionMemeDate;
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

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function isAppaire(): bool
    {
        return $this->appaire;
    }

    public function setAppaire(bool $appaire): void
    {
        $this->appaire = $appaire;
    }

    /**
     * @return Collection<int, Appariement>
     */
    public function getAppariementsParticipant(): Collection
    {
        return $this->appariementsParticipant;
    }

    /**
     * @return Collection<int, Appariement>
     */
    public function getAppariementsEntreprise(): Collection
    {
        return $this->appariementsEntreprise;
    }

    /**
     * @return Collection<int, Appariement>
     */
    public function getAppariements(): Collection
    {
        return Type::PARTICIPANT === $this->type ? $this->appariementsParticipant : $this->appariementsEntreprise;
    }
}

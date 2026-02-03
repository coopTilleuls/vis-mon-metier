<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\Table(name: 'commune')]
class Commune
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Le nom de la commune ne peut pas être vide')]
    private string $nom;

    #[ORM\ManyToOne(targetEntity: ModelQuestionnaire::class, inversedBy: 'communes')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ModelQuestionnaire $modelQuestionnaire;

    #[ORM\Column]
    private int $position = 0;

    /**
     * @var Collection<int, Contact>
     */
    #[ORM\OneToMany(targetEntity: Contact::class, mappedBy: 'communeEntity')]
    private Collection $contacts;

    public function __construct()
    {
        $this->contacts = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;

        return $this;
    }

    public function getModelQuestionnaire(): ModelQuestionnaire
    {
        return $this->modelQuestionnaire;
    }

    public function setModelQuestionnaire(ModelQuestionnaire $modelQuestionnaire): self
    {
        $this->modelQuestionnaire = $modelQuestionnaire;

        return $this;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function setPosition(int $position): self
    {
        $this->position = $position;

        return $this;
    }

    /**
     * @return Collection<int, Contact>
     */
    public function getContacts(): Collection
    {
        return $this->contacts;
    }

    public function __toString(): string
    {
        return $this->nom;
    }
}

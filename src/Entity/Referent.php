<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\ReferentRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ReferentRepository::class)]
class Referent
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $email = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $fonction = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $structure = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $prenom = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $nomFamille = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $telephone = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'referents')]
    private ?User $utilisateur = null;

    public function getId(): int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getFonction(): ?string
    {
        return $this->fonction;
    }

    public function setFonction(?string $fonction): static
    {
        $this->fonction = $fonction;

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

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(?string $telephone): static
    {
        $this->telephone = $telephone;

        return $this;
    }

    public function getUtilisateur(): ?User
    {
        return $this->utilisateur;
    }

    public function setUtilisateur(?User $utilisateur): static
    {
        $this->utilisateur = $utilisateur;

        return $this;
    }

    public function getStructure(): ?string
    {
        return $this->structure;
    }

    public function setStructure(?string $structure): void
    {
        $this->structure = $structure;
    }

    public function __toString(): string
    {
        if ($this->getPrenom() || $this->getNomFamille() || $this->getStructure()) {
            return "{$this->getPrenom()} {$this->getNomFamille()} {$this->getFonction()} {$this->getStructure()}";
        }

        return 'Non précisé';
    }
}

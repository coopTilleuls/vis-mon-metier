<?php

declare(strict_types=1);

namespace App\Entity;

use App\ValueObject\Type;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\ObjectMapper\Attribute\Map;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\Table(name: 'contact')]
class Contact
{
    public const DEMANDEUR_EMPLOI = 'Demandeur·euse d\'emploi';
    public const SCOLAIRE_ETUDIANT = 'Scolaire, Etudiant·e';
    public const SALARIE = 'Salarié·e';
    public const FONCTIONNAIRE = 'Fonctionnaire';
    public const AUTRE = 'Autre';
    public const MAJEUR = 'Plus de 18 ans';
    public const MINEUR = 'Moins de 18 ans';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Map(if: false)]
    private int $id;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $nomEntreprise = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $secteurActivite = null;

    #[ORM\Column(length: 255)]
    #[Assert\Choice(choices: [Type::EMPLOYEUR, Type::PARTICIPANT], message: 'Type invalide')]
    private string $type;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $nombreEmployes = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $prenom = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $nomFamille = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $numeroTelephone = null;

    #[ORM\Column(length: 50, nullable: true)]
    #[Assert\Choice(choices: [self::SALARIE, self::DEMANDEUR_EMPLOI, self::SCOLAIRE_ETUDIANT, self::FONCTIONNAIRE, self::AUTRE], message: 'Type invalide')]
    private ?string $situation = null;

    #[ORM\Column(length: 50, nullable: true)]
    #[Assert\Choice(choices: [self::MAJEUR, self::MINEUR], message: 'Type invalide')]
    private ?string $trancheAge = null;

    #[ORM\Column(length: 255)]
    private string $email;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $commune = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $fonctionSignataire = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $prenomTuteur = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $nomFamilleTuteur = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $fonctionTuteur = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $emailTuteur = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $telephoneTuteur = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $adresseEntreprise = null;

    #[ORM\Column(length: 14, nullable: true)]
    private ?string $siret = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $horaires = null;

    #[ORM\Column(nullable: true)]
    #[Map(if: false)]
    private ?bool $epiDisponible = null;

    #[ORM\Column(nullable: true)]
    #[Map(if: false)]
    private ?bool $autresMesuresPrevention = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $precisionMesuresPrevention = null;

    #[ORM\Column(nullable: true)]
    #[Map(if: false)]
    private ?bool $consentementRgpd = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    #[Map(if: false)]
    private ?\DateTimeImmutable $consentementRgpdAt = null;

    #[ORM\ManyToOne(targetEntity: Referent::class, cascade: ['persist'])]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    #[Map(if: false)]
    private ?Referent $referent = null;

    public function getId(): int
    {
        return $this->id;
    }

    public function getNomEntreprise(): ?string
    {
        return $this->nomEntreprise;
    }

    public function setNomEntreprise(?string $nomEntreprise): static
    {
        $this->nomEntreprise = $nomEntreprise;

        return $this;
    }

    public function getSecteurActivite(): ?string
    {
        return $this->secteurActivite;
    }

    public function setSecteurActivite(?string $secteurActivite): static
    {
        $this->secteurActivite = $secteurActivite;

        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }

    public function getNombreEmployes(): ?string
    {
        return $this->nombreEmployes;
    }

    public function setNombreEmployes(?string $nombreEmployes): static
    {
        $this->nombreEmployes = $nombreEmployes;

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

    public function getNumeroTelephone(): ?string
    {
        return $this->numeroTelephone;
    }

    public function setNumeroTelephone(?string $numeroTelephone): static
    {
        $this->numeroTelephone = $numeroTelephone;

        return $this;
    }

    public function getSituation(): ?string
    {
        return $this->situation;
    }

    public function setSituation(?string $situation): void
    {
        $this->situation = $situation;
    }

    public function getTrancheAge(): ?string
    {
        return $this->trancheAge;
    }

    public function setTrancheAge(?string $trancheAge): static
    {
        $this->trancheAge = $trancheAge;

        return $this;
    }

    public function getReferent(): ?Referent
    {
        return $this->referent;
    }

    public function setReferent(?Referent $referent): static
    {
        $this->referent = $referent;

        return $this;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getCommune(): ?string
    {
        return $this->commune;
    }

    public function setCommune(?string $commune): void
    {
        $this->commune = $commune;
    }

    public function getFonctionSignataire(): ?string
    {
        return $this->fonctionSignataire;
    }

    public function setFonctionSignataire(?string $fonctionSignataire): void
    {
        $this->fonctionSignataire = $fonctionSignataire;
    }

    public function getPrenomTuteur(): ?string
    {
        return $this->prenomTuteur;
    }

    public function setPrenomTuteur(?string $prenomTuteur): void
    {
        $this->prenomTuteur = $prenomTuteur;
    }

    public function getNomFamilleTuteur(): ?string
    {
        return $this->nomFamilleTuteur;
    }

    public function setNomFamilleTuteur(?string $nomFamilleTuteur): void
    {
        $this->nomFamilleTuteur = $nomFamilleTuteur;
    }

    public function getFonctionTuteur(): ?string
    {
        return $this->fonctionTuteur;
    }

    public function setFonctionTuteur(?string $fonctionTuteur): void
    {
        $this->fonctionTuteur = $fonctionTuteur;
    }

    public function getEmailTuteur(): ?string
    {
        return $this->emailTuteur;
    }

    public function setEmailTuteur(?string $emailTuteur): void
    {
        $this->emailTuteur = $emailTuteur;
    }

    public function getTelephoneTuteur(): ?string
    {
        return $this->telephoneTuteur;
    }

    public function setTelephoneTuteur(?string $telephoneTuteur): void
    {
        $this->telephoneTuteur = $telephoneTuteur;
    }

    public function getAdresseEntreprise(): ?string
    {
        return $this->adresseEntreprise;
    }

    public function setAdresseEntreprise(?string $adresseEntreprise): void
    {
        $this->adresseEntreprise = $adresseEntreprise;
    }

    public function getSiret(): ?string
    {
        return $this->siret;
    }

    public function setSiret(?string $siret): void
    {
        $this->siret = $siret;
    }

    public function getHoraires(): ?string
    {
        return $this->horaires;
    }

    public function setHoraires(?string $horaires): void
    {
        $this->horaires = $horaires;
    }

    public function getEpiDisponible(): ?bool
    {
        return $this->epiDisponible;
    }

    public function setEpiDisponible(?bool $epiDisponible): void
    {
        $this->epiDisponible = $epiDisponible;
    }

    public function getAutresMesuresPrevention(): ?bool
    {
        return $this->autresMesuresPrevention;
    }

    public function setAutresMesuresPrevention(?bool $autresMesuresPrevention): void
    {
        $this->autresMesuresPrevention = $autresMesuresPrevention;
    }

    public function getPrecisionMesuresPrevention(): ?string
    {
        return $this->precisionMesuresPrevention;
    }

    public function setPrecisionMesuresPrevention(?string $precisionMesuresPrevention): void
    {
        $this->precisionMesuresPrevention = $precisionMesuresPrevention;
    }

    public function getConsentementRgpd(): ?bool
    {
        return $this->consentementRgpd;
    }

    public function setConsentementRgpd(?bool $consentementRgpd): void
    {
        $this->consentementRgpd = $consentementRgpd;
    }

    public function getConsentementRgpdAt(): ?\DateTimeImmutable
    {
        return $this->consentementRgpdAt;
    }

    public function setConsentementRgpdAt(?\DateTimeImmutable $consentementRgpdAt): void
    {
        $this->consentementRgpdAt = $consentementRgpdAt;
    }

    public function __toString(): string
    {
        return "{$this->getPrenom()} {$this->getNomFamille()}";
    }
}

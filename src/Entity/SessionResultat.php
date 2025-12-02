<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class SessionResultat
{
    public const STATUT_PROPOSE = 'propose';
    public const STATUT_VALIDE_REFERENT = 'valide_referent';
    public const STATUT_ACCEPTE_EMPLOYEUR = 'accepte_employeur';
    public const STATUT_REFUSE = 'refuse';
    public const STATUT_REALISE = 'realise';
    public const STATUT_ANNULE = 'annule';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    #[ORM\ManyToOne(targetEntity: Session::class)]
    #[ORM\JoinColumn(nullable: false)]
    private Session $session;

    #[ORM\ManyToOne(targetEntity: Questionnaire::class)]
    #[ORM\JoinColumn(nullable: false)]
    private Questionnaire $questionnaireParticipant;

    #[ORM\ManyToOne(targetEntity: Questionnaire::class)]
    #[ORM\JoinColumn(nullable: false)]
    private Questionnaire $questionnaireEmployeur;

    #[ORM\Column]
    private int $score;

    #[ORM\Column(length: 50)]
    private string $statut = self::STATUT_PROPOSE;

    #[ORM\Column]
    private \DateTimeImmutable $dateCreation;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $commentaire = null;

    public function __construct()
    {
        $this->dateCreation = new \DateTimeImmutable();
    }

    public function getId(): ?int
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

    public function getQuestionnaireParticipant(): Questionnaire
    {
        return $this->questionnaireParticipant;
    }

    public function setQuestionnaireParticipant(Questionnaire $questionnaireParticipant): void
    {
        $this->questionnaireParticipant = $questionnaireParticipant;
    }

    public function getQuestionnaireEmployeur(): Questionnaire
    {
        return $this->questionnaireEmployeur;
    }

    public function setQuestionnaireEmployeur(Questionnaire $questionnaireEmployeur): void
    {
        $this->questionnaireEmployeur = $questionnaireEmployeur;
    }

    public function getScore(): int
    {
        return $this->score;
    }

    public function setScore(int $score): void
    {
        $this->score = $score;
    }

    public function getStatut(): string
    {
        return $this->statut;
    }

    public function setStatut(string $statut): void
    {
        $this->statut = $statut;
    }

    public function getDateCreation(): \DateTimeImmutable
    {
        return $this->dateCreation;
    }

    public function setDateCreation(\DateTimeImmutable $dateCreation): void
    {
        $this->dateCreation = $dateCreation;
    }

    public function getCommentaire(): ?string
    {
        return $this->commentaire;
    }

    public function setCommentaire(?string $commentaire): void
    {
        $this->commentaire = $commentaire;
    }
}

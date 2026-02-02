<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Questionnaire;
use App\Entity\Roles;
use App\Entity\User;
use App\Security\Voter\QuestionnaireVoter;
use App\ValueObject\Type;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Config\KeyValueStore;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response;

/**
 * @extends AbstractCrudController<Questionnaire>
 */
class QuestionnaireCrudController extends AbstractCrudController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly AdminUrlGenerator $adminUrlGenerator,
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return Questionnaire::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        if ($this->isGranted(Roles::REFERENT) && !$this->isGranted(Roles::ADMIN)) {
            $crud->setDefaultSort(['type' => 'DESC', 'createdAt' => 'DESC']);
        } else {
            $crud->setDefaultSort(['createdAt' => 'DESC']);
        }

        return $crud
            ->setEntityLabelInSingular('Questionnaire')
            ->setEntityLabelInPlural('Questionnaires')
            ->setPageTitle('index', 'Liste des questionnaires remplis')
            ->overrideTemplate('crud/detail', 'admin/questionnaire/detail.html.twig')
            ->showEntityActionsInlined()
            ->setSearchFields(null)
        ;
    }

    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        $qb = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);

        if ($this->isGranted(Roles::ADMIN)) {
            return $qb;
        }

        // If Referent, restrict to Linked candidates OR all Employeurs
        if ($this->isGranted(Roles::REFERENT)) {
            /** @var User $user */
            $user = $this->getUser();
            $referentIds = $user->getReferents()->map(fn ($r) => $r->getId())->toArray();

            $qb->leftJoin('entity.contact', 'c');

            if (empty($referentIds)) {
                $qb->andWhere('entity.type = :typeEmployeur')
                    ->setParameter('typeEmployeur', Type::EMPLOYEUR);
            } else {
                $qb->andWhere($qb->expr()->orX(
                    'c.referent IN (:referentIds)',
                    'entity.type = :typeEmployeur'
                ))
                    ->setParameter('referentIds', $referentIds)
                    ->setParameter('typeEmployeur', Type::EMPLOYEUR);
            }

            return $qb;
        }

        return $qb;
    }

    public function configureActions(Actions $actions): Actions
    {
        $editAnswers = Action::new('editAnswers', 'Modifier Réponses')
            ->linkToRoute('admin_questionnaire_edit_answers', function (Questionnaire $questionnaire): array {
                return ['id' => $questionnaire->getId()];
            })
            ->setIcon('fa fa-pen')
            ->setLabel(false)
            ->setHtmlAttributes(['title' => 'Modifier Réponses']);

        $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->add(Crud::PAGE_INDEX, $editAnswers)
            ->add(Crud::PAGE_DETAIL, $editAnswers)
            ->remove(Crud::PAGE_INDEX, Action::NEW)
            ->remove(Crud::PAGE_INDEX, Action::EDIT)
            ->disable(Action::NEW, Action::EDIT)

            ->update(Crud::PAGE_INDEX, Action::DETAIL, function (Action $action) {
                return $action
                    ->setIcon('fa-solid fa-eye')
                    ->setLabel(false)
                    ->setHtmlAttributes(['title' => 'Consulter']);
            })
            ->update(Crud::PAGE_INDEX, Action::DELETE, function (Action $action) {
                return $action
                    ->setIcon('fa fa-trash')
                    ->setLabel(false)
                    ->setHtmlAttributes(['title' => 'Supprimer']);
            })

            ->setPermission(Action::DETAIL, QuestionnaireVoter::VIEW)
            ->setPermission(Action::DELETE, QuestionnaireVoter::DELETE)
            ->setPermission('editAnswers', QuestionnaireVoter::EDIT_ANSWERS)
            ->setPermission(Action::BATCH_DELETE, Roles::ADMIN)
        ;

        return $actions;
    }

    public function configureFields(string $pageName): iterable
    {
        if ($this->isGranted(Roles::ADMIN)) {
            yield AssociationField::new('session', 'Session')
                ->autocomplete()
                ->setRequired(true)
                ->hideOnForm();
        } else {
            yield TextField::new('session', 'Session')
                ->hideOnForm();
        }

        yield AssociationField::new('contact', 'Contact')
            ->setRequired(true)
            ->hideOnForm();

        yield TextField::new('metier', 'Métier');

        yield ChoiceField::new('type', 'Type de profil')
            ->setChoices(Type::choiceOptions())
            ->renderAsBadges([
                Type::EMPLOYEUR => 'secondary',
                Type::PARTICIPANT => 'primary',
            ])
            ->hideOnForm();

        yield DateTimeField::new('createdAt', 'Créé le')
            ->hideOnForm();
    }

    public function configureFilters(Filters $filters): Filters
    {
        return parent::configureFilters($filters)
            ->add('session')
            ->add(
                ChoiceFilter::new('type', 'Type de profil')
                    ->setChoices([
                        Type::EMPLOYEUR => Type::EMPLOYEUR,
                        Type::PARTICIPANT => Type::PARTICIPANT,
                    ])
            )
            ->add('metier');
    }

    public function detail(AdminContext $context): KeyValueStore|Response
    {
        /** @var Questionnaire $questionnaire */
        $questionnaire = $context->getEntity()->getInstance();

        $this->denyAccessUnlessGranted(QuestionnaireVoter::VIEW, $questionnaire);

        $matches = $this->getTopMatches($questionnaire);

        $context->getRequest()->attributes->set('matches', $matches);
        $context->getRequest()->attributes->set('questionnaire', $questionnaire);

        return parent::detail($context);
    }

    /**
     * @return array<mixed>
     */
    private function getTopMatches(Questionnaire $questionnaire): array
    {
        $session = $questionnaire->getSession();
        $targetType = Type::EMPLOYEUR === $questionnaire->getType() ? Type::PARTICIPANT : Type::EMPLOYEUR;

        $qb = $this->entityManager->getRepository(Questionnaire::class)->createQueryBuilder('q')
            ->where('q.session = :session')
            ->andWhere('q.type = :targetType')
            ->setParameter('session', $session)
            ->setParameter('targetType', $targetType);

        if ($this->isGranted(Roles::REFERENT) && !$this->isGranted(Roles::ADMIN) && Type::PARTICIPANT === $targetType) {
            /** @var User $user */
            $user = $this->getUser();
            $referentIds = $user->getReferents()->map(fn ($r) => $r->getId())->toArray();

            $qb->leftJoin('q.contact', 'c');

            if (empty($referentIds)) {
                $qb->andWhere('1 = 0');
            } else {
                $qb->andWhere('c.referent IN (:referentIds)')
                    ->setParameter('referentIds', $referentIds);
            }
        }

        $candidates = $qb->getQuery()->getResult();

        $currentBusyDates = [];
        $existingMatchesCount = 0;
        foreach ($questionnaire->getAppariements() as $match) {
            ++$existingMatchesCount;
            if ($match->getDateMatch()) {
                $currentBusyDates[] = $match->getDateMatch()->format('d/m');
            }
        }

        $results = [];
        $currentScores = $questionnaire->getReponsesScore() ?? [];
        $questionsSchema = $session->getModelQuestionnaire()->getQuestionsDisposition();
        $currentDates = $this->extractDatesFromAnswers($questionnaire->getReponsesDisposition() ?? [], $questionsSchema);

        foreach ($candidates as $candidate) {
            $candidateScores = $candidate->getReponsesScore() ?? [];

            if (Type::EMPLOYEUR === $questionnaire->getType()) {
                $score = $this->calculateMatchScore($currentScores, $candidateScores);
            } else {
                $score = $this->calculateMatchScore($candidateScores, $currentScores);
            }

            $contact = $candidate->getContact();
            $name = $contact ? sprintf('%s %s', $contact->getPrenom(), $contact->getNomFamille()) : 'Inconnu';
            $label = $name;
            $entreprise = ($contact && Type::EMPLOYEUR === $candidate->getType()) ? $contact->getNomEntreprise() : null;

            $candidateDates = $this->extractDatesFromAnswers($candidate->getReponsesDisposition() ?? [], $questionsSchema);
            $matchingDates = array_intersect($currentDates, $candidateDates);

            $constraintMismatches = $this->getConstraintMismatches(
                $questionnaire,
                $candidate,
                $questionsSchema
            );

            $isMatchedWithCurrent = false;
            $candidateBusyDates = [];
            foreach ($candidate->getAppariements() as $match) {
                if ($match->getDateMatch()) {
                    $candidateBusyDates[] = $match->getDateMatch()->format('d/m');
                }

                $partner = (Type::PARTICIPANT === $candidate->getType()) ? $match->getEntreprise() : $match->getParticipant();
                if ($partner && $partner->getId() === $questionnaire->getId()) {
                    $isMatchedWithCurrent = true;
                }
            }

            $detailUrl = $this->adminUrlGenerator
                ->setController(self::class)
                ->setAction(Action::DETAIL)
                ->setEntityId($candidate->getId())
                ->generateUrl();

            $formattedDates = [];
            foreach ($matchingDates as $dateStr) {
                $safeDateStr = str_replace('/', '-', $dateStr);
                $matchUrl = $this->generateUrl('admin_questionnaire_match', [
                    'sourceId' => $questionnaire->getId(),
                    'targetId' => $candidate->getId(),
                    'date' => $safeDateStr,
                ]);

                $isAvailable = true;
                $unavailableReason = '';

                if (in_array($dateStr, $currentBusyDates, true)) {
                    $isAvailable = false;
                    $unavailableReason = 'Vous êtes indisponible';
                } elseif (in_array($dateStr, $candidateBusyDates, true)) {
                    $isAvailable = false;
                    $unavailableReason = 'Candidat indisponible';
                }

                $formattedDates[] = [
                    'label' => $dateStr,
                    'url' => $matchUrl,
                    'isAvailable' => $isAvailable,
                    'reason' => $unavailableReason,
                ];
            }

            $results[] = [
                'label' => $label,
                'name' => $name,
                'entreprise' => $entreprise,
                'job' => $candidate->getMetier(),
                'score' => $score,
                'detailUrl' => $detailUrl,
                'trancheAge' => $contact->getTrancheAge(),
                'compatibleDates' => !empty($formattedDates) ? $formattedDates : false,
                'email' => $contact->getEmail(),
                'phone' => $contact->getNumeroTelephone(),
                'address' => $contact->getCommune(),
                'contactEntity' => $contact,
                'isMatchedWithCurrent' => $isMatchedWithCurrent,
                'constraintMismatches' => $constraintMismatches,
            ];
        }

        usort($results, fn ($a, $b) => $b['score'] <=> $a['score']);

        return array_slice($results, 0, 10 + $existingMatchesCount);
    }

    /**
     * @param array<mixed> $answers
     * @param array<mixed> $schema
     *
     * @return array<mixed>
     */
    private function extractDatesFromAnswers(array $answers, array $schema): array
    {
        $normalized = [];
        $questions = $schema['questions'] ?? [];
        $targetKey = null;

        foreach ($questions as $index => $question) {
            if (isset($question['type_reponse']) && 'choix dates multiples' === $question['type_reponse']) {
                $targetKey = 'question_'.$index;
                break;
            }
        }

        if (null !== $targetKey && isset($answers[$targetKey]) && is_array($answers[$targetKey])) {
            foreach ($answers[$targetKey] as $dateStr) {
                try {
                    $normalized[] = new \DateTime($dateStr)->format('d/m');
                } catch (\Exception) {
                }
            }
        }

        return array_unique($normalized);
    }

    /**
     * @param array<mixed> $employerAnswers
     * @param array<mixed> $participantAnswers
     */
    public function calculateMatchScore(array $employerAnswers, array $participantAnswers): int
    {
        $totalDistance = 0;
        $maxDistancePossible = 0;
        foreach ($employerAnswers as $criteriaId => $employerValue) {
            if (!isset($participantAnswers[$criteriaId])) {
                continue;
            }
            $participantValue = $participantAnswers[$criteriaId];
            $distance = abs($employerValue - $participantValue);
            $totalDistance += $distance;
            $maxDistancePossible += 3;
        }

        if (0 === $maxDistancePossible) {
            return 0;
        }

        return (int) (100 - (($totalDistance / $maxDistancePossible) * 100));
    }

    /**
     * @param array<mixed> $questionsSchema
     *
     * @return array<string>
     */
    private function getConstraintMismatches(Questionnaire $employeur, Questionnaire $participant, array $questionsSchema): array
    {
        $mismatches = [];
        $questions = $questionsSchema['questions'] ?? [];

        $employeurAnswers = Type::EMPLOYEUR === $employeur->getType()
            ? $employeur->getReponsesDisposition() ?? []
            : $participant->getReponsesDisposition() ?? [];

        $participantAnswers = Type::PARTICIPANT === $employeur->getType()
            ? $employeur->getReponsesDisposition() ?? []
            : $participant->getReponsesDisposition() ?? [];

        foreach ($questions as $index => $question) {
            if (!isset($question['contrainte_majeure']) || true !== $question['contrainte_majeure']) {
                continue;
            }

            $key = 'question_'.$index;
            $employeurValue = $employeurAnswers[$key] ?? null;
            $participantValue = $participantAnswers[$key] ?? null;

            if (null === $employeurValue || null === $participantValue) {
                continue;
            }

            $isMismatch = false;
            $typeReponse = $question['type_reponse'] ?? 'choix unique';

            if ('choix multiples' === $typeReponse) {
                $employeurArray = is_array($employeurValue) ? $employeurValue : [$employeurValue];
                $participantArray = is_array($participantValue) ? $participantValue : [$participantValue];

                $intersection = array_intersect($employeurArray, $participantArray);
                $isMismatch = empty($intersection);
            } else {
                // single choice case
                $isMismatch = $employeurValue !== $participantValue;
            }

            if ($isMismatch) {
                $label = $question['label_participant'] ?? $question['label_employeur'] ?? 'Question '.$index;
                $mismatches[] = $label;
            }
        }

        return $mismatches;
    }
}

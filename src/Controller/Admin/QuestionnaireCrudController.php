<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Questionnaire;
use App\Service\MatchingHelper;
use App\ValueObject\Type;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Config\KeyValueStore;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response;

/**
 * @extends AbstractCrudController<Questionnaire>
 */
class QuestionnaireCrudController extends AbstractCrudController
{
    public function __construct(
        private readonly MatchingHelper $matchingHelper,
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
        return $crud
            ->setEntityLabelInSingular('Questionnaire')
            ->setEntityLabelInPlural('Questionnaires')
            ->setPageTitle('index', 'Liste des questionnaires remplis')
            ->setDefaultSort(['createdAt' => 'DESC'])
            ->overrideTemplate('crud/detail', 'admin/questionnaire/detail.html.twig')
            ->showEntityActionsInlined()
        ;
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

        return $actions
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
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        yield AssociationField::new('session', 'Session')
            ->autocomplete()
            ->setRequired(true)
            ->hideOnForm();

        yield AssociationField::new('contact', 'Contact')
            ->setRequired(true)
            ->hideOnForm();

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
            );
    }

    public function detail(AdminContext $context): KeyValueStore|Response
    {
        /** @var Questionnaire $questionnaire */
        $questionnaire = $context->getEntity()->getInstance();
        $matches = $this->getTopMatches($questionnaire);

        $context->getRequest()->attributes->set('matches', $matches);
        $context->getRequest()->attributes->set('questionnaire', $questionnaire);

        return parent::detail($context);
    }

    /**
     * @return array<int, array{label: string, name: string, entreprise: ?string, job: ?string, score: int, detailUrl: string, compatibleDates: array<string>|false, matchingDates: array<string>, email: string, phone: string, address: string, contactEntity: ?\App\Entity\Contact}>
     */
    private function getTopMatches(Questionnaire $questionnaire): array
    {
        $session = $questionnaire->getSession();
        $targetType = Type::EMPLOYEUR === $questionnaire->getType() ? Type::PARTICIPANT : Type::EMPLOYEUR;

        $candidates = $this->entityManager->getRepository(Questionnaire::class)->findBy([
            'session' => $session,
            'type' => $targetType,
        ]);

        $results = [];
        $currentScores = $questionnaire->getReponsesScore() ?? [];
        $currentDates = $this->extractAndNormalizeDates($questionnaire->getReponsesDisposition() ?? []);

        foreach ($candidates as $candidate) {
            $candidateScores = $candidate->getReponsesScore() ?? [];

            if (Type::EMPLOYEUR === $questionnaire->getType()) {
                $score = $this->matchingHelper->calculateMatchScore($currentScores, $candidateScores);
            } else {
                $score = $this->matchingHelper->calculateMatchScore($candidateScores, $currentScores);
            }

            $contact = $candidate->getContact();
            $name = $contact ? sprintf('%s %s', $contact->getPrenom(), $contact->getNomFamille()) : 'Inconnu';
            $label = $name;

            $entreprise = ($contact && Type::EMPLOYEUR === $candidate->getType()) ? $contact->getNomEntreprise() : null;

            $candidateDates = $this->extractAndNormalizeDates($candidate->getReponsesDisposition() ?? []);
            $matchingDates = array_intersect($currentDates, $candidateDates);

            $url = $this->adminUrlGenerator
                ->setController(self::class)
                ->setAction(Action::DETAIL)
                ->setEntityId($candidate->getId())
                ->generateUrl();

            $results[] = [
                'label' => $label,
                'name' => $name,
                'entreprise' => $entreprise,
                'job' => $candidate->getMetier(),
                'score' => $score,
                'detailUrl' => $url,
                'compatibleDates' => !empty($matchingDates) ? $matchingDates : false,
                'matchingDates' => $matchingDates,
                'email' => $contact ? $contact->getEmail() : '',
                'phone' => $contact ? ($contact->getNumeroTelephone() ?? '') : '',
                'address' => $contact ? $contact->getCommune() : '',
                'contactEntity' => $contact,
            ];
        }

        usort($results, fn ($a, $b) => $b['score'] <=> $a['score']);

        return array_slice($results, 0, 10);
    }

    /**
     * @param array<mixed> $reponses
     *
     * @return array<string>
     */
    private function extractAndNormalizeDates(array $reponses): array
    {
        $normalized = [];

        foreach ($reponses as $responseValue) {
            if (!is_array($responseValue)) {
                continue;
            }

            $first = reset($responseValue);
            if (is_string($first) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $first)) {
                foreach ($responseValue as $dateStr) {
                    try {
                        $normalized[] = new \DateTime($dateStr)->format('d/m');
                    } catch (\Exception) {
                        // Ignore invalid dates
                    }
                }
            }
        }

        return array_unique($normalized);
    }
}

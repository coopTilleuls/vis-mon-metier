<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Roles;
use App\Entity\Session;
use App\Repository\AppariementRepository;
use App\Repository\QuestionnaireRepository;
use App\ValueObject\Type;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\KeyValueStore;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Attribute\Route;

/**
 * @extends AbstractCrudController<Session>
 */
class SessionCrudController extends AbstractCrudController
{
    public function __construct(
        private readonly QuestionnaireRepository $questionnaireRepository,
        private readonly AppariementRepository $appariementRepository,
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return Session::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setPageTitle(Crud::PAGE_INDEX, 'Liste des sessions')
            ->setPageTitle(Crud::PAGE_NEW, 'Créer une session')
            ->setPageTitle(Crud::PAGE_EDIT, 'Modifier la session')
            ->setPageTitle(Crud::PAGE_DETAIL, fn (Session $session) => $session->getNom())
            ->setEntityLabelInSingular('Session')
            ->setEntityLabelInPlural('Sessions')
            ->setDefaultSort(['id' => 'DESC'])
            ->showEntityActionsInlined()
            ->setEntityPermission(Roles::ADMIN)
            ->overrideTemplate('crud/detail', 'admin/session/detail.html.twig')
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('nom', 'Nom de la session')
            ->hideOnDetail();
        yield DateField::new('dateDebut', 'Date début session');
        yield DateField::new('dateFin', 'Date fin session');
        yield ChoiceField::new('statut', 'Statut de la session')
            ->setChoices(Session::getStatutChoices());
        yield BooleanField::new('inscriptionVolontaireOuverte', 'Inscriptions volontaires ouvertes')
            ->renderAsSwitch(true)
            ->setHelp('Autorise l\'accès au formulaire volontaire (la session doit aussi être « ouverte »).');
        yield BooleanField::new('inscriptionEntrepriseOuverte', 'Inscriptions entreprises ouvertes')
            ->renderAsSwitch(true)
            ->setHelp('Autorise l\'accès au formulaire professionnel·le (la session doit aussi être « ouverte »).');
        yield AssociationField::new('modelQuestionnaire', 'Questionnaire à associer')
            ->autocomplete()
            ->setRequired(false)
            ->hideOnIndex();
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->update(Crud::PAGE_INDEX, Action::DETAIL, function (Action $action) {
                return $action
                    ->setIcon('fa-solid fa-eye')
                    ->setLabel(false)
                    ->setHtmlAttributes(['title' => 'Consulter']);
            })
            ->update(Crud::PAGE_INDEX, Action::EDIT, function (Action $action) {
                return $action
                    ->setIcon('fa fa-pen')
                    ->setLabel(false)
                    ->setHtmlAttributes(['title' => 'Modifier']);
            })

            ->update(Crud::PAGE_INDEX, Action::DELETE, function (Action $action) {
                return $action
                    ->setIcon('fa fa-trash')
                    ->setLabel(false)
                    ->setHtmlAttributes(['title' => 'Supprimer']);
            })

            ->update(Crud::PAGE_INDEX, Action::NEW, function (Action $action) {
                return $action->setLabel('Créer une session');
            })
            ->remove(Crud::PAGE_EDIT, Action::SAVE_AND_CONTINUE)
            ->remove(Crud::PAGE_NEW, Action::SAVE_AND_ADD_ANOTHER)
            ->reorder(Crud::PAGE_INDEX, [Action::DETAIL, Action::EDIT, Action::DELETE])
        ;
    }

    public function detail(AdminContext $context): KeyValueStore|Response
    {
        /** @var Session $session */
        $session = $context->getEntity()->getInstance();

        $questionnaireRepository = $this->questionnaireRepository;

        $stats = [
            'sessionId' => $session->getId(),
            'sessionName' => $session->getNom(),
            'participants' => $questionnaireRepository->countActiveParticipants($session),
            'inactiveParticipants' => $questionnaireRepository->countInactiveParticipants($session),
            'jobs' => $questionnaireRepository->countJobs($session),
            'appariement' => $this->appariementRepository->countBySession($session),
            'byAge' => $questionnaireRepository->getParticipantsByAge($session),
            'bySituation' => $questionnaireRepository->getParticipantsBySituation($session),
            'byAccompaniment' => $questionnaireRepository->getParticipantsByAccompaniment($session),
            'byCommune' => $questionnaireRepository->getEnterprisesByCommune($session),
            'byCommuneParticipants' => $questionnaireRepository->getParticipantsByCommune($session),
            'byDateCount' => $questionnaireRepository->getParticipantsByDateCount($session),
            'byQuestions' => $questionnaireRepository->getStatisticsByQuestion($session),
        ];

        $context->getRequest()->attributes->set('stats', $stats);

        return parent::detail($context);
    }

    #[Route('/admin/session/{id}/export-metiers', name: 'admin_session_export_metiers')]
    public function exportMetiers(Session $session): Response
    {
        $questionnaires = $this->questionnaireRepository->createQueryBuilder('q')
            ->join('q.contact', 'c')
            ->where('q.session = :session')
            ->andWhere('q.type = :type')
            ->setParameter('session', $session)
            ->setParameter('type', Type::EMPLOYEUR)
            ->getQuery()
            ->getResult();

        $response = new StreamedResponse(function () use ($questionnaires) {
            $handle = fopen('php://output', 'wb');
            if (false === $handle) {
                return;
            }

            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, ['Secteur d\'activité', 'Métier'], ';');

            foreach ($questionnaires as $questionnaire) {
                $contact = $questionnaire->getContact();
                fputcsv($handle, [
                    $contact?->getSecteurActivite() ?? '',
                    $questionnaire->getMetier() ?? '',
                ], ';');
            }

            fclose($handle);
        });

        $response->headers->set('Content-Type', 'text/csv; charset=utf-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="export-metiers-'.trim($session->getNom()).'.csv"');

        return $response;
    }
}

<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Brand\BrandProvider;
use App\Entity\Appariement;
use App\Entity\Contact;
use App\Entity\ModelQuestionnaire;
use App\Entity\Questionnaire;
use App\Entity\Referent;
use App\Entity\Roles;
use App\Entity\Session;
use App\Entity\User;
use App\Repository\AppariementRepository;
use App\Repository\QuestionnaireRepository;
use App\Service\MatchScoreCalculator;
use App\ValueObject\StatutImmersion;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[AdminDashboard(routePath: '/admin', routeName: 'admin')]
class AdminDashboardController extends AbstractDashboardController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly QuestionnaireRepository $questionnaireRepository,
        private readonly AppariementRepository $appariementRepository,
        private readonly BrandProvider $brandProvider,
        private readonly MatchScoreCalculator $matchScoreCalculator,
        private readonly AdminUrlGenerator $adminUrlGenerator,
    ) {
    }

    public function index(): Response
    {
        $stats = null;
        $sessionRepository = $this->entityManager->getRepository(Session::class);
        $openSession = $sessionRepository->findOneBy(['statut' => Session::STATUT_OUVERT], ['dateDebut' => 'DESC', 'dateFin' => 'DESC']);
        // Fall back to the most recent *closed* session so the dashboard keeps
        // showing the last session that took place even once it is closed.
        // Draft (brouillon) sessions are intentionally excluded.
        $displaySession = $openSession ?? $sessionRepository->findOneBy(['statut' => Session::STATUT_FERME], ['dateDebut' => 'DESC', 'dateFin' => 'DESC']);

        if ($displaySession) {
            $questionnaireRepository = $this->questionnaireRepository;
            $stats = [
                'sessionId' => $displaySession->getId(),
                'sessionName' => $displaySession->getNom(),
                'participants' => $questionnaireRepository->countActiveParticipants($displaySession),
                'inactiveParticipants' => $questionnaireRepository->countInactiveParticipants($displaySession),
                'jobs' => $questionnaireRepository->countJobs($displaySession),
                'appariement' => $this->appariementRepository->countBySession($displaySession),
                'byAge' => $questionnaireRepository->getParticipantsByAge($displaySession),
                'bySituation' => $questionnaireRepository->getParticipantsBySituation($displaySession),
                'byAccompaniment' => $questionnaireRepository->getParticipantsByAccompaniment($displaySession),
                'byCommune' => $questionnaireRepository->getEnterprisesByCommune($displaySession),
                'byCommuneParticipants' => $questionnaireRepository->getParticipantsByCommune($displaySession),
                'byDateCount' => $questionnaireRepository->getParticipantsByDateCount($displaySession),
                'byQuestions' => $questionnaireRepository->getStatisticsByQuestion($displaySession),
            ];
        }

        return $this->render('admin/dashboard.html.twig', [
            'stats' => $stats,
            'hasActiveSession' => (bool) $openSession,
            'inscriptionVolontaireOuverte' => $openSession?->isInscriptionVolontaireOuverte() ?? false,
            'inscriptionEntrepriseOuverte' => $openSession?->isInscriptionEntrepriseOuverte() ?? false,
        ]);
    }

    #[Route('/admin/suivi-immersions', name: 'admin_suivi_immersions')]
    public function suiviImmersions(Request $request): Response
    {
        $sessionRepository = $this->entityManager->getRepository(Session::class);
        $sessions = $sessionRepository->findBy([], ['id' => 'DESC']);

        $requestedSessionId = $request->query->getInt('session');
        $session = $requestedSessionId > 0
            ? $sessionRepository->find($requestedSessionId)
            : $sessionRepository->findOneBy(['statut' => Session::STATUT_OUVERT], ['id' => 'DESC']);

        // Rôle : ADMIN filtre librement ; REFERENT est verrouillé sur ses propres référents.
        $isAdmin = $this->isGranted(Roles::ADMIN);
        $referentIds = null;
        $selectedReferentId = null;
        $referents = [];

        if ($isAdmin) {
            $referents = $this->entityManager->getRepository(Referent::class)->findBy([], ['nomFamille' => 'ASC', 'prenom' => 'ASC']);
            $selectedReferentId = $request->query->getInt('referent') ?: null;
            if (null !== $selectedReferentId) {
                $referentIds = [$selectedReferentId];
            }
        } else {
            /** @var User $user */
            $user = $this->getUser();
            $referentIds = $user->getReferents()->map(fn (Referent $r) => $r->getId())->toArray();
        }

        $showPhantom = $request->query->getBoolean('sansImmersion');

        $rows = [];
        $recap = null;

        if (null !== $session) {
            foreach ($this->appariementRepository->findForSuivi($session, $referentIds) as $immersion) {
                $rows[] = $this->buildImmersionRow($immersion);
            }

            if ($showPhantom) {
                foreach ($this->questionnaireRepository->findParticipantsSansImmersion($session, $referentIds) as $participant) {
                    $rows[] = $this->buildPhantomRow($participant);
                }
            }

            $recap = [
                'volontaires' => count(array_unique(array_filter(array_column($rows, 'volontaireId')))),
                'immersions' => count(array_filter($rows, fn ($r) => !$r['isPhantom'])),
                'entreprises' => count(array_unique(array_filter(array_column($rows, 'entrepriseId')))),
                'sansImmersion' => $this->questionnaireRepository->countParticipantsSansImmersion($session, $referentIds),
                'sousQuota' => $isAdmin ? $this->questionnaireRepository->countEnterprisesUnderQuota($session) : null,
            ];
        }

        return $this->render('admin/suivi_immersions/index.html.twig', [
            'sessions' => $sessions,
            'session' => $session,
            'referents' => $referents,
            'selectedReferentId' => $selectedReferentId,
            'isAdmin' => $isAdmin,
            'showPhantom' => $showPhantom,
            'statutChoices' => StatutImmersion::cases(),
            'rows' => $rows,
            'recap' => $recap,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function buildImmersionRow(Appariement $immersion): array
    {
        $participant = $immersion->getParticipant();
        $entreprise = $immersion->getEntreprise();
        $participantContact = $participant?->getContact();
        $entrepriseContact = $entreprise?->getContact();
        $referent = $participantContact?->getReferent();

        $score = $this->matchScoreCalculator->calculate(
            $entreprise?->getReponsesScore() ?? [],
            $participant?->getReponsesScore() ?? [],
        );

        return [
            'isPhantom' => false,
            'immersionId' => $immersion->getId(),
            'volontaireId' => $participant?->getId(),
            'volontaire' => trim(sprintf('%s %s', $participantContact?->getPrenom() ?? '', $participantContact?->getNomFamille() ?? '')),
            'volontaireUrl' => $participant ? $this->questionnaireDetailUrl($participant->getId()) : null,
            'commune' => $participantContact?->getCommune(),
            'situation' => $participantContact?->getSituation(),
            'referent' => $referent ? trim(sprintf('%s %s', $referent->getPrenom(), $referent->getNomFamille())) : null,
            'entrepriseId' => $entreprise?->getId(),
            'entreprise' => $entrepriseContact?->getNomEntreprise(),
            'entrepriseUrl' => $entreprise ? $this->questionnaireDetailUrl($entreprise->getId()) : null,
            'metier' => $entreprise?->getMetier(),
            'date' => $immersion->getDateMatch(),
            'statut' => $immersion->getStatut(),
            'observations' => $immersion->getObservations(),
            'observationsVolontaire' => $participantContact?->getObservationsVolontaire(),
            'email' => $participantContact?->getEmail(),
            'telephone' => $participantContact?->getNumeroTelephone(),
            'score' => $score,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function buildPhantomRow(Questionnaire $participant): array
    {
        $contact = $participant->getContact();
        $referent = $contact?->getReferent();

        return [
            'isPhantom' => true,
            'immersionId' => null,
            'volontaireId' => $participant->getId(),
            'volontaire' => trim(sprintf('%s %s', $contact?->getPrenom() ?? '', $contact?->getNomFamille() ?? '')),
            'volontaireUrl' => $this->questionnaireDetailUrl($participant->getId()),
            'commune' => $contact?->getCommune(),
            'situation' => $contact?->getSituation(),
            'referent' => $referent ? trim(sprintf('%s %s', $referent->getPrenom(), $referent->getNomFamille())) : null,
            'entrepriseId' => null,
            'entreprise' => null,
            'entrepriseUrl' => null,
            'metier' => null,
            'date' => null,
            'statut' => null,
            'observations' => null,
            'observationsVolontaire' => $contact?->getObservationsVolontaire(),
            'email' => $contact?->getEmail(),
            'telephone' => $contact?->getNumeroTelephone(),
            'score' => null,
        ];
    }

    private function questionnaireDetailUrl(int $id): string
    {
        return $this->adminUrlGenerator
            ->setController(QuestionnaireCrudController::class)
            ->setAction(Action::DETAIL)
            ->setEntityId($id)
            ->generateUrl();
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Vis mon métier - '.$this->brandProvider->getName())
            ->disableDarkMode();
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Tableau de bord', 'fa fa-home');
        yield MenuItem::linkToCrud('Sessions', 'fas fa-calendar-days', Session::class)
            ->setPermission(Roles::ADMIN);
        yield MenuItem::linkToCrud('Questionnaires', 'fas fa-question', ModelQuestionnaire::class)
            ->setPermission(Roles::ADMIN);
        yield MenuItem::linkToCrud('Contacts', 'fas fa-users', Contact::class);
        yield MenuItem::linkToCrud('Résultats de matchings', 'fas fa-clipboard-list', Questionnaire::class);
        yield MenuItem::linkToRoute('Suivi des immersions', 'fas fa-table-list', 'admin_suivi_immersions');
        yield MenuItem::linkToCrud('Référent·es', 'fas fa-person', Referent::class)
            ->setPermission(Roles::ADMIN);
        yield MenuItem::linkToCrud('Utilisateur·ices', 'fas fa-user', User::class)
            ->setPermission(Roles::ADMIN);
    }
}

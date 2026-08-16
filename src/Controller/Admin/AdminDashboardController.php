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
use EasyCorp\Bundle\EasyAdminBundle\Provider\AdminContextProvider;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

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
        private readonly AdminContextProvider $adminContextProvider,
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
        // Atteinte en direct (URL brute tapée à la main), la route ne dispose pas de
        // l'AdminContext (`ea`) requis par le layout EasyAdmin dès que le dashboard a
        // été servi dans le worker → 500. On rejoue alors la requête via la route du
        // dashboard EA (qui construit le contexte), en conservant les filtres GET.
        if (null === $this->adminContextProvider->getContext()) {
            return $this->redirectToRoute('admin', array_merge(
                $request->query->all(),
                ['routeName' => 'admin_suivi_immersions'],
            ));
        }

        $sessionRepository = $this->entityManager->getRepository(Session::class);
        $sessions = $sessionRepository->findBy([], ['id' => 'DESC']);

        // Lecture brute puis cast : getInt() lève une 400 sur une valeur non entière
        // (chaîne vide ou non numérique) — même précaution que pour le filtre référent.
        $sessionParam = $request->query->get('session');
        $requestedSessionId = (null !== $sessionParam && '' !== $sessionParam) ? (int) $sessionParam : 0;
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
            // Option « Tous » soumet une valeur vide : ne pas utiliser getInt() qui lève une 400 sur chaîne vide.
            $referentParam = $request->query->get('referent');
            $selectedReferentId = (null !== $referentParam && '' !== $referentParam) ? (int) $referentParam : null;
            if (null !== $selectedReferentId) {
                $referentIds = [$selectedReferentId];
            }
        } else {
            /** @var User $user */
            $user = $this->getUser();
            $referentIds = array_values($user->getReferents()->map(fn (Referent $r) => $r->getId())->toArray());
        }

        $showPhantom = $request->query->getBoolean('sansImmersion');

        $rows = [];
        $recap = null;

        if (null !== $session) {
            $immersionRows = [];
            foreach ($this->appariementRepository->findForSuivi($session, $referentIds) as $immersion) {
                $immersionRows[] = $this->buildImmersionRow($immersion);
            }

            $rows = $immersionRows;
            if ($showPhantom) {
                foreach ($this->questionnaireRepository->findParticipantsSansImmersion($session, $referentIds) as $participant) {
                    $rows[] = $this->buildPhantomRow($participant);
                }
            }

            // Récap calculé sur l'ensemble des immersions du périmètre (référent) et
            // indépendant de l'affichage des lignes « sans immersion ». Les compteurs se
            // recoupent : volontaires = (avec immersion) + (sans immersion).
            $volontairesSansImmersion = $this->questionnaireRepository->countParticipantsSansImmersion($session, $referentIds);
            $volontairesAvecImmersion = count(array_unique(array_filter(array_column($immersionRows, 'volontaireId'))));

            $recap = [
                'volontaires' => $volontairesAvecImmersion + $volontairesSansImmersion,
                'immersions' => count($immersionRows),
                'entreprisesAvecImmersion' => count(array_unique(array_filter(array_column($immersionRows, 'entrepriseId')))),
                'volontairesSansImmersion' => $volontairesSansImmersion,
                'entreprisesDisponibles' => $isAdmin ? $this->questionnaireRepository->countEnterprisesAvailableForImmersion($session) : null,
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

    #[Route('/admin/suivi-immersions/immersion/{id}', name: 'admin_suivi_immersion_update', methods: ['POST'])]
    #[IsGranted(Roles::REFERENT)]
    public function updateImmersion(Appariement $immersion, Request $request): JsonResponse
    {
        if (!$this->isCsrfTokenValid('suivi_immersion_'.$immersion->getId(), (string) $request->request->get('_token'))) {
            return new JsonResponse(['error' => 'Jeton CSRF invalide.'], Response::HTTP_FORBIDDEN);
        }

        if (!$this->canEditImmersion($immersion)) {
            return new JsonResponse(['error' => 'Accès refusé.'], Response::HTTP_FORBIDDEN);
        }

        if ($request->request->has('statut')) {
            $statut = StatutImmersion::tryFrom((string) $request->request->get('statut'));
            if (null === $statut) {
                return new JsonResponse(['error' => 'Statut invalide.'], Response::HTTP_BAD_REQUEST);
            }
            $immersion->setStatut($statut);
        }

        if ($request->request->has('observations')) {
            $observations = trim((string) $request->request->get('observations'));
            $immersion->setObservations('' !== $observations ? $observations : null);
        }

        $this->entityManager->flush();

        return new JsonResponse([
            'ok' => true,
            'statut' => $immersion->getStatut()->value,
            'label' => $immersion->getStatut()->label(),
            'couleur' => $immersion->getStatut()->couleur(),
        ]);
    }

    #[Route('/admin/suivi-immersions/volontaire/{id}', name: 'admin_suivi_volontaire_update', methods: ['POST'])]
    #[IsGranted(Roles::REFERENT)]
    public function updateVolontaire(Questionnaire $participant, Request $request): JsonResponse
    {
        if (!$this->isCsrfTokenValid('suivi_volontaire_'.$participant->getId(), (string) $request->request->get('_token'))) {
            return new JsonResponse(['error' => 'Jeton CSRF invalide.'], Response::HTTP_FORBIDDEN);
        }

        if (!$this->canEditVolontaire($participant)) {
            return new JsonResponse(['error' => 'Accès refusé.'], Response::HTTP_FORBIDDEN);
        }

        $contact = $participant->getContact();
        if (null === $contact) {
            return new JsonResponse(['error' => 'Contact introuvable.'], Response::HTTP_NOT_FOUND);
        }

        $observations = trim((string) $request->request->get('observations'));
        $contact->setObservationsVolontaire('' !== $observations ? $observations : null);
        $this->entityManager->flush();

        return new JsonResponse(['ok' => true]);
    }

    /**
     * Référents du user courant, ou null s'il est ADMIN (aucune restriction).
     *
     * @return list<int>|null
     */
    private function referentScopeIds(): ?array
    {
        if ($this->isGranted(Roles::ADMIN)) {
            return null;
        }

        /** @var User $user */
        $user = $this->getUser();

        return array_values($user->getReferents()->map(fn (Referent $r) => $r->getId())->toArray());
    }

    private function canEditVolontaire(Questionnaire $participant): bool
    {
        $scopeIds = $this->referentScopeIds();
        if (null === $scopeIds) {
            return true;
        }

        $referent = $participant->getContact()?->getReferent();

        return null !== $referent && in_array($referent->getId(), $scopeIds, true);
    }

    private function canEditImmersion(Appariement $immersion): bool
    {
        $participant = $immersion->getParticipant();

        return null !== $participant && $this->canEditVolontaire($participant);
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
        yield MenuItem::linkToCrud('Référent·es', 'fas fa-person', Referent::class)
            ->setPermission(Roles::ADMIN);
        yield MenuItem::linkToCrud('Utilisateur·ices', 'fas fa-user', User::class)
            ->setPermission(Roles::ADMIN);
        yield MenuItem::linkToRoute('Suivi des immersions', 'fas fa-table-list', 'admin_suivi_immersions');
    }
}

<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Brand\BrandProvider;
use App\Entity\Contact;
use App\Entity\ModelQuestionnaire;
use App\Entity\Questionnaire;
use App\Entity\Referent;
use App\Entity\Roles;
use App\Entity\Session;
use App\Entity\User;
use App\Repository\AppariementRepository;
use App\Repository\QuestionnaireRepository;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;

#[AdminDashboard(routePath: '/admin', routeName: 'admin')]
class AdminDashboardController extends AbstractDashboardController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly QuestionnaireRepository $questionnaireRepository,
        private readonly AppariementRepository $appariementRepository,
        private readonly BrandProvider $brandProvider,
    ) {
    }

    public function index(): Response
    {
        $stats = null;
        $sessionRepository = $this->entityManager->getRepository(Session::class);
        $openSession = $sessionRepository->findOneBy(['statut' => Session::STATUT_OUVERT], ['id' => 'DESC']);
        // Fall back to the most recent session so the dashboard keeps showing
        // the last session that took place even once it is closed.
        $displaySession = $openSession ?? $sessionRepository->findOneBy([], ['id' => 'DESC']);

        if ($displaySession) {
            $questionnaireRepository = $this->questionnaireRepository;
            $stats = [
                'sessionId' => $displaySession->getId(),
                'sessionName' => $displaySession->getNom(),
                'participants' => $questionnaireRepository->countParticipants($displaySession),
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
        ]);
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
    }
}

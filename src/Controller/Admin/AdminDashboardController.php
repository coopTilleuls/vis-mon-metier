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
        $activeSession = $this->entityManager->getRepository(Session::class)->findOneBy(['statut' => Session::STATUT_OUVERT], ['id' => 'DESC']);

        if ($activeSession) {
            $questionnaireRepository = $this->questionnaireRepository;
            $stats = [
                'sessionId' => $activeSession->getId(),
                'sessionName' => $activeSession->getNom(),
                'participants' => $questionnaireRepository->countParticipants($activeSession),
                'jobs' => $questionnaireRepository->countJobs($activeSession),
                'appariement' => $this->appariementRepository->countBySession($activeSession),
                'byAge' => $questionnaireRepository->getParticipantsByAge($activeSession),
                'bySituation' => $questionnaireRepository->getParticipantsBySituation($activeSession),
                'byAccompaniment' => $questionnaireRepository->getParticipantsByAccompaniment($activeSession),
                'byCommune' => $questionnaireRepository->getEnterprisesByCommune($activeSession),
                'byCommuneParticipants' => $questionnaireRepository->getParticipantsByCommune($activeSession),
                'byDateCount' => $questionnaireRepository->getParticipantsByDateCount($activeSession),
                'byQuestions' => $questionnaireRepository->getStatisticsByQuestion($activeSession),
            ];
        }

        return $this->render('admin/dashboard.html.twig', [
            'stats' => $stats,
            'hasActiveSession' => (bool) $activeSession,
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
        yield MenuItem::linkToCrud('Questions', 'fas fa-question', ModelQuestionnaire::class)
            ->setPermission(Roles::ADMIN);
        yield MenuItem::linkToCrud('Contacts', 'fas fa-users', Contact::class);
        yield MenuItem::linkToCrud('Questionnaires remplis', 'fas fa-clipboard-list', Questionnaire::class);
        yield MenuItem::linkToCrud('Référent·es', 'fas fa-person', Referent::class)
            ->setPermission(Roles::ADMIN);
        yield MenuItem::linkToCrud('Utilisateur·ices', 'fas fa-user', User::class)
            ->setPermission(Roles::ADMIN);
    }
}

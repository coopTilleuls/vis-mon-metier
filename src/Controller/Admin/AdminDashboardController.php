<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\ModelQuestionnaire;
use App\Entity\Session;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;

#[AdminDashboard(routePath: '/admin', routeName: 'admin')]
class AdminDashboardController extends AbstractDashboardController
{
    public function index(): Response
    {
        return $this->render('/admin/dashboard.html.twig');
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Vis mon métier - Pays de Blain');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToCrud('Sessions', 'fas fa-calendar-days', Session::class);
        yield MenuItem::linkToCrud('Questions', 'fas fa-question', ModelQuestionnaire::class);
    }
}

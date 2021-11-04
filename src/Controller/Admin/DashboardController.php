<?php

namespace App\Controller\Admin;

use App\Entity\Project;
use App\Entity\Times;
use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractDashboardController
{
    const USER_NOT_FOUND = 'user not found';

    /** @var LoggerInterface  */
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @Route("/admin", name="admin")
     */
    public function index(): Response
    {
        $routeBuilder = $this->get(AdminUrlGenerator::class);
        $user = $this->getUser();
        if (!in_array('ROLE_ADMIN', $user->getRoles())) {
            try {
                $adminInstanceId = $user->getId();
            } catch (\Exception $e) {

                return new Response(self::USER_NOT_FOUND);
            }

            return $this->redirect($routeBuilder->setController(UserCrudController::class)->setAction(Action::DETAIL)->setEntityId($adminInstanceId)
                ->generateUrl());
        }

        return $this->redirect($routeBuilder->setController(UserCrudController::class)->generateUrl());
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()->setTitle('Sio')
            ->setFaviconPath('')
            ->setTextDirection('ltr')
            ->setTranslationDomain('admin')
            ->renderContentMaximized();;
    }

    public function configureMenuItems(): iterable
    {
        return [
            MenuItem::section('Users')->setPermission('ROLE_ADMIN'),
            MenuItem::linkToCrud('Users', 'fa fa-tags', User::class)->setPermission('ROLE_ADMIN'),
            MenuItem::section('Projects')->setPermission('ROLE_ADMIN'),
            MenuItem::linkToCrud('Add project', 'fa fa-tags', Project::class)->setAction('new')->setPermission('ROLE_ADMIN'),
            MenuItem::subMenu('Projects', 'fa fa-tags')->setSubItems($this->getProjectTimesByUser())->setPermission('ROLE_USER'),
            MenuItem::linkToRoute('Summarize per day', 'fa fa-chart-bar', 'summarizeDay')->setPermission('ROLE_ADMIN'),
            MenuItem::linkToRoute('Summarize per month', 'fa fa-chart-bar', 'summarizeMonth')->setPermission('ROLE_ADMIN')
        ];
        //yield MenuItem::linktoDashboard('Dashboard', 'fa fa-home');
        // yield MenuItem::linkToCrud('The Label', 'fas fa-list', EntityClass::class);
    }

    public function getProjectTimesByUser()
    {
        $linksArray = [];
        $user = $this->getUser();
        $projects = $user->getProjects();
        if (count($projects) === 0) {
            return [];
        }
        foreach ($projects as $project) {
            $linksArray[] = MenuItem::linkToCrud($project->getName(), 'fa fa-file-text', Times::class)->setQueryParameter('project', $project->getId())->setQueryParameter('user', $user);
        }

        return $linksArray;
    }
}

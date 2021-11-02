<?php

namespace App\Controller\Admin;

use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractDashboardController
{
    const USER_NOT_FOUND = 'user not found';

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
            MenuItem::linkToCrud('Users', 'fa fa-tags', User::class)->setPermission('ROLE_ADMIN')
        ];
        //yield MenuItem::linktoDashboard('Dashboard', 'fa fa-home');
        // yield MenuItem::linkToCrud('The Label', 'fas fa-list', EntityClass::class);
    }
}

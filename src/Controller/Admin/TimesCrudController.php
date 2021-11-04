<?php

namespace App\Controller\Admin;

use App\Entity\Times;
use App\Service\GetTimeIntervalService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TimesCrudController extends AbstractCrudController
{
    /** @var RequestStack  */
    private $requestStack;

    /** @var GetTimeIntervalService  */
    private $getTimeIntervalService;

    /** @var EntityManagerInterface  */
    private $entityManager;

    public function __construct(RequestStack $requestStack, GetTimeIntervalService $getTimeIntervalService, EntityManagerInterface $entityManager)
    {
        $this->requestStack = $requestStack;
        $this->getTimeIntervalService = $getTimeIntervalService;
        $this->entityManager = $entityManager;
    }

    public static function getEntityFqcn(): string
    {
        return Times::class;
    }

    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        $startedAt = $this->requestStack->getCurrentRequest()->get('startedAt');
        $finishedAt = $this->requestStack->getCurrentRequest()->get('finishedAt');
        $qb = $this->entityManager->getRepository(Times::class)->createQueryBuilder('t');
        if ($startedAt && $finishedAt) {
            $qb = $this->entityManager->getRepository(Times::class)->qbForTimeInterval($startedAt, $finishedAt);
        }

        return $qb;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            AssociationField::new('user'),
            AssociationField::new('project'),
            DateTimeField::new('startedAt'),
            DateTimeField::new('finishedAt'),
            Field::new('isDeleted'),
            Field::new('diff')->setLabel('working time')->hideOnForm()
        ];
    }

    public function configureActions(Actions $actions): Actions
    {
        $startStopAction = $this->startStopButton();
        $currentDayButton = $this->currentDayButton();
        $currentMonthButton = $this->currentMonthButton();
        $exportButton = $this->exportButton();

        return $actions->setPermission(Action::EDIT, 'ROLE_ADMIN')->setPermission(Action::DELETE, 'ROLE_ADMIN')
            ->setPermission(Action::NEW, 'ROLE_ADMIN')
            ->add(Crud::PAGE_INDEX, $startStopAction)
            ->add(Crud::PAGE_INDEX, $exportButton)
            ->add(Crud::PAGE_INDEX, $currentMonthButton)
            ->add(Crud::PAGE_INDEX, $currentDayButton);
    }

    private function startStopButton(): Action
    {
        $adminUrlGenerator = $this->get(AdminUrlGenerator::class);
        $url = $adminUrlGenerator->setController(TimesCrudController::class)->setAction('ajaxAction')
            ->set('project', $this->requestStack->getCurrentRequest()->get('project'))->generateUrl();
        $startStopAction = Action::new('start', 'start')
            ->setIcon('fa fa-cloud-action')
            ->linkToCrudAction('startStopAction')->createAsGlobalAction()->displayAsButton()->setCssClass('btn alert-notify start')
            ->setHtmlAttributes(['url' => $url]);

        return $startStopAction;
    }

    private function currentDayButton(): Action
    {
        return Action::new('day', 'current day')
            ->setIcon('fa-solid fa-1')
            ->linkToCrudAction('oneDayAction')->createAsGlobalAction()->displayAsLink();
    }

    public function oneDayAction()
    {
        $adminUrlGenerator = $this->get(AdminUrlGenerator::class);
        $oneDayInterval = $this->getTimeIntervalService->prepareDayInterval();

        return $this->redirect($adminUrlGenerator->setController(TimesCrudController::class)
            ->setAction(Action::INDEX)->set('startedAt', $oneDayInterval[0])->set('finishedAt', $oneDayInterval[1]));
    }

    private function currentMonthButton(): Action
    {
        return Action::new('month', 'current month')
            ->setIcon('fa-solid fa-30')
            ->linkToCrudAction('oneMonthAction')->createAsGlobalAction()->displayAsLink();
    }

    public function oneMonthAction()
    {
        $adminUrlGenerator = $this->get(AdminUrlGenerator::class);
        $oneMonthInterval = $this->getTimeIntervalService->prepareMonthInterval();

        return $this->redirect($adminUrlGenerator->setController(TimesCrudController::class)
            ->setAction(Action::INDEX)->set('startedAt', $oneMonthInterval[0])->set('finishedAt', $oneMonthInterval[1]));
    }

    private function exportButton(): Action
    {
        return Action::new('export', 'export')
            ->setIcon('fa fa-file')
            ->linkToCrudAction('exportAction')->createAsGlobalAction()->displayAsButton();
    }

    /**
     *@Route ("/ajaxAction", name = "ajaxAction")
     */
    public function ajaxAction(): Response
    {
        $action = $this->requestStack->getCurrentRequest()->get('action');
        $timeString = $this->requestStack->getCurrentRequest()->get('time');
        $projectId = $this->requestStack->getCurrentRequest()->get('project');
        $this->getTimeIntervalService->addDataToDatabase($action, $timeString, $projectId, $this->getUser());

        return new Response('1');
    }

    public function configureAssets(Assets $assets): Assets
    {
        return $assets->addCssFile('build/css/project.css')
            ->addJsFile('build/js/jquery.min.js')
            ->addJsFile('build/js/project.js');
    }
}

<?php


namespace App\Controller\Admin;


use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Service\GetTimeIntervalService;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

class SummarizeController extends AbstractController
{
    /** @var GetTimeIntervalService  */
    private $getIntervalService;

    public function __construct(GetTimeIntervalService $getTimeIntervalService)
    {
        $this->getIntervalService = $getTimeIntervalService;
    }

    /**
     * @Route ("/admin/summarizeDay", name="summarizeDay")
     */
    public function summarizeDay(): Response
    {
        $perDay = $this->getIntervalService->prepareDayInterval();

        return $this->render('admin/summarize/index.html.twig', ['data' => $this->getIntervalService->summary($perDay[0], $perDay[1]), 'period' => 'per day']);
    }

    /**
     * @Route ("/admin/summarizeMonth", name="summarizeMonth")
     */
    public function summarizeMonth(): Response
    {
        $perDay = $this->getIntervalService->prepareMonthInterval();

        return $this->render('admin/summarize/index.html.twig', ['data' => $this->getIntervalService->summary($perDay[0], $perDay[1]), 'period' => 'per day']);
    }

}
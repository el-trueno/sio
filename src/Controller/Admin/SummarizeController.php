<?php


namespace App\Controller\Admin;


use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Service\GetTimeIntervalService;
use Symfony\Component\Routing\Annotation\Route;

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
    public function summarizeDay()
    {
        $perDay = $this->getIntervalService->prepareDayInterval(); //dd($this->getIntervalService->summary($perDay[0], $perDay[1]));

        return $this->render('admin/summarize/index.html.twig', ['data' => $this->getIntervalService->summary($perDay[0], $perDay[1]), 'period' => 'per day']);
    }

    /**
     * @Route ("/admin/summarizeMonth", name="summarizeMonth")
     */
    public function summarizeMonth()
    {
        $perDay = $this->getIntervalService->prepareMonthInterval(); //dd($this->getIntervalService->summary($perDay[0], $perDay[1]));

        return $this->render('admin/summarize/index.html.twig', ['data' => $this->getIntervalService->summary($perDay[0], $perDay[1]), 'period' => 'per day']);
    }

}
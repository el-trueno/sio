<?php


namespace App\Service;


use App\Entity\Project;
use App\Entity\User;
use App\Repository\TimesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

class ExportService
{
    const FILENAME = 'export';

    /** @var TimesRepository  */
    private $timesRepository;

    /** @var EntityManagerInterface  */
    private $entityManager;

    /** @var LoggerInterface  */
    private $logger;

    public function __construct(TimesRepository $timesRepository, EntityManagerInterface $entityManager, LoggerInterface $logger)
    {
        $this->timesRepository = $timesRepository;
        $this->entityManager = $entityManager;
        $this->logger = $logger;
    }

    public function exportXls(string $userString = null, string $projectString = null, string $startedAt = null, string $finishedAt = null): void
    {
        $user = $userString ? $this->entityManager->getRepository(User::class)->find($userString) : null;
        $project = $projectString ? $this->entityManager->getRepository(Project::class)->find($projectString) : null;
        $queryData = $this->timesRepository->getDataPerPeriod($user, $project, $startedAt, $finishedAt);
        $this->prepareXls($queryData);
    }

    private function prepareXls(array $queryData): void
    {
        try {
            $separator = ';';
            $newString = "\n";
            header('Content-Type: application/xls');
            header('Content-Disposition: attachment; filename=' . self::FILENAME . '.xls');
            header('Pragma: no-cache');
            header('Expires: 0');
            $headerArray = ['id', 'name', 'project', 'started', 'finished', 'time'];
            $headerString = implode($separator, $headerArray) . $newString;
            $bodyString = '';
            foreach ($queryData as $times) {
                $startedAtObj = $times->getStartedAt();
                $finishedAtObj = $times->getFinishedAt();
                $startedAt = $startedAtObj ? $startedAtObj->format('Y-m-d H:i:s') : null;
                $finishedAt = $finishedAtObj ? $finishedAtObj->format('Y-m-d H:i:s') : null;
                $interval = $finishedAtObj->diff($startedAtObj);
                $bodyStringArray = [$times->getId(), $times->getUser(), $times->getProject(), $startedAt, $finishedAt, $interval->format('%a days %h hours %i minutes %s seconds')];
                $bodyString .= implode($separator, $bodyStringArray) . $newString;
            }
            echo $headerString . $bodyString;
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }
}
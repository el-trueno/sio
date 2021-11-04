<?php

namespace App\Service;

use App\Entity\Project;
use App\Entity\Times;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

class GetTimeIntervalService
{
    /** @var EntityManagerInterface  */
    private $entityManager;

    /** @var LoggerInterface  */
    private $logger;

    public function __construct(EntityManagerInterface $entityManager, LoggerInterface $logger)
    {
        $this->entityManager = $entityManager;
        $this->logger = $logger;
    }

    public function prepareDayInterval(): array
    {
        $date = new \DateTimeImmutable("now");

        return [$date->format('Y-m-d 00:00:00'), $date->format('Y-m-d 23:59:59')];
    }

    public function prepareMonthInterval(): array
    {
        $date = new \DateTimeImmutable("now");

        return [$date->format('Y-m-01 00:00:00'), $date->format('Y-m-t 23:59:59')];
    }

    public function prepareTime(\DateTimeImmutable $workingTime, string $timeString): \DateTimeImmutable
    {
        $startDate = new \DateTimeImmutable("now");
        if (trim($timeString) === '' || !$timeString) {
            return $startDate;
        }
        $timeArray = explode(":", $timeString);
        $timeInterval = 'PT' . $timeArray[0] . 'H' . $timeArray[1] . 'M' . $timeArray[2] . 'S';

        return $workingTime->add(new \DateInterval($timeInterval));
    }

    public function addDataToDatabase(string $action, string $workingTime = null, int $projectId, User $user): void
    {
        try {
            $project = $this->entityManager->getRepository(Project::class)->find($projectId);
            if ($action === 'start') {
                $times = new Times();
                $times->setUser($user);
                $times->setProject($project);
                $times->setStartedAt(new \DateTimeImmutable("now"));
                $this->entityManager->persist($times);
            } else {
                $timesObject = $this->entityManager->getRepository(Times::class)->findOneBy(['project' => $project, 'user' => $user], ['id' => 'DESC'], 1, 0);
                $preparedTime = $this->prepareTime($timesObject->getStartedAt(), $workingTime);
                $timesObject->setFinishedAt($preparedTime);
            }
            $this->entityManager->flush();
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function summary(string $startedAt, string $finishedAt): array
    {
        $data = $this->entityManager->getRepository(Times::class)->summarize($startedAt, $finishedAt);
        $result = [];
        foreach ($data as $value) {
            $result[] = [$value[0], gmdate("H:i:s", $value[1])];
        }

        return $result;
    }
}
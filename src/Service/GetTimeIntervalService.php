<?php

namespace App\Service;

class GetTimeIntervalService
{
    const ABSENT_INPUT_DATE = 'Absent input date';

    private function prepareDayInterval(string $dateTime): array
    {
        if (!$dateTime) {
            throw new \Exception(self::ABSENT_INPUT_DATE);
        }
        $date = new \DateTimeImmutable($dateTime);

        return [$date->format('Y-m-d 00:00:00'), $date->format('Y-m-d 23:59:59')];
    }

    private function prepareMonthInterval(int $month, int $year): array
    {
        if (!$month || !$year) {
            throw new \Exception(self::ABSENT_INPUT_DATE);
        }
        $month = $month < 10 ? '0'.$month : $month;
        $dateStart = new \DateTimeImmutable($year.'-'.$month.'- 01 00:00:00');
        $dateStartFormatted = $dateStart->format('Y-m-d H:i:s');
        
    }

    public function getTimeForDay(string $start, string $end): string
    {

    }
}
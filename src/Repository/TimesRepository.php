<?php

namespace App\Repository;

use App\Entity\Project;
use App\Entity\Times;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Times|null find($id, $lockMode = null, $lockVersion = null)
 * @method Times|null findOneBy(array $criteria, array $orderBy = null)
 * @method Times[]    findAll()
 * @method Times[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TimesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Times::class);
    }

    public function getDataPerPeriod(User $user = null, Project $project = null, string $dateStart = null, string $dateEnd = null): array
    {
         return $this->qbForTimeInterval($dateStart, $dateEnd, $user, $project)->getQuery()->getResult();
    }

    public function qbForTimeInterval(string $dateStart = null, string $dateEnd = null, User $user = null, Project $project = null): QueryBuilder
    {
        $qb = $this->createQueryBuilder('t');
        if ($dateStart) {
            $qb->where('t.startedAt >= :dateStart')->setParameter('dateStart', $dateStart);
        }
        if ($dateEnd) {
            $qb->andWhere('t.finishedAt <= :dateEnd')->setParameter('dateEnd', $dateEnd);
        }
        if ($project) {
            $qb->andWhere('t.project = :project')->setParameter('project', $project);
        }
        if ($user) {
            $qb->andWhere('t.user = :user')->setParameter('user', $user);
            $qb->andWhere('t.isDeleted = :isDeleted')->setParameter('isDeleted', false);
        }

        return $qb;
    }

    public function summarize(string $startedAt, string $finishedAt): array
    {
        return $this->createQueryBuilder('t')
            ->select('t')
            ->addSelect('sum(unix_timestamp(t.finishedAt) - unix_timestamp(t.startedAt))')
            ->leftJoin('t.user', 'u')
            ->where('t.startedAt >= :startedAt')
            ->andWhere('t.finishedAt <= :finishedAt')
            ->groupBy('t.user')
            ->addGroupBy('t.project')
            ->setParameter('startedAt', $startedAt)
            ->setParameter('finishedAt', $finishedAt)
            ->getQuery()->getResult();
    }
}

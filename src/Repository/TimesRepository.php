<?php

namespace App\Repository;

use App\Entity\Project;
use App\Entity\Times;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Times|null find($id, $lockMode = null, $lockVersion = null)
 * @method Times|null findOneBy(array $criteria, array $orderBy = null)
 * @method Times[]    findAll()
 * @method Times[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TimesRepository extends ServiceEntityRepository
{
    const INPUT_DATA_ERROR = 'Error in input data';

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Times::class);
    }

    public function getDataPerPeriod(User $user, Project $project = null, string $dateStart, string $dateEnd): array
    {
        if (!$dateStart || !$dateEnd || !$user) {
            throw new \Exception(self::INPUT_DATA_ERROR);
        }
        $qb = $this->createQueryBuilder('t')
            ->where('t.user = :user')
            ->andWhere('t.startedAt >= :dateStart AND t.finishedAt <= :dateEnd');
        if ($project) {
            $qb->andWhere('t.project = :project')->setParameter('project', $project);
        }
        $qb->setParameters(['user' => $user, 'dateStart' => $dateStart, 'dateEnd' => $dateEnd]);

        return $qb->getQuery()->getArrayResult();
    }

    // /**
    //  * @return Times[] Returns an array of Times objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('t.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Times
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}

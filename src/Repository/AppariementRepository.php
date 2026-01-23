<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Appariement;
use App\Entity\Session;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Appariement>
 */
class AppariementRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Appariement::class);
    }

    public function countBySession(Session $session): int
    {
        return (int) $this->createQueryBuilder('a')
            ->select('count(a.id)')
            ->leftJoin('a.participant', 'p')
            ->where('p.session = :session')
            ->setParameter('session', $session)
            ->getQuery()
            ->getSingleScalarResult();
    }
}

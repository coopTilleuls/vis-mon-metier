<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Questionnaire;
use App\Entity\Session;
use App\ValueObject\Type;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Questionnaire>
 */
class QuestionnaireRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Questionnaire::class);
    }

    public function countParticipants(Session $session): int
    {
        return (int) $this->createQueryBuilder('q')
            ->select('count(q.id)')
            ->where('q.session = :session')
            ->andWhere('q.type = :type')
            ->setParameter('session', $session)
            ->setParameter('type', Type::PARTICIPANT)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countJobs(Session $session): int
    {
        return (int) $this->createQueryBuilder('q')
            ->select('count(q.id)')
            ->where('q.session = :session')
            ->andWhere('q.type = :type')
            ->setParameter('session', $session)
            ->setParameter('type', Type::EMPLOYEUR)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @return array<mixed>
     */
    public function getParticipantsByAge(Session $session): array
    {
        return $this->createQueryBuilder('q')
            ->select('c.trancheAge as label, count(q.id) as count')
            ->join('q.contact', 'c')
            ->where('q.session = :session')
            ->andWhere('q.type = :type')
            ->groupBy('c.trancheAge')
            ->setParameter('session', $session)
            ->setParameter('type', Type::PARTICIPANT)
            ->getQuery()
            ->getResult();
    }

    /**
     * @return array<mixed>
     */
    public function getParticipantsBySituation(Session $session): array
    {
        return $this->createQueryBuilder('q')
            ->select('c.situation as label, count(q.id) as count')
            ->join('q.contact', 'c')
            ->where('q.session = :session')
            ->andWhere('q.type = :type')
            ->groupBy('c.situation')
            ->setParameter('session', $session)
            ->setParameter('type', Type::PARTICIPANT)
            ->getQuery()
            ->getResult();
    }
}

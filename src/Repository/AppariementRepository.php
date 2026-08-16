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
    use ReferentFilterTrait;

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

    /**
     * Immersions d'une session pour la vue de suivi, avec fetch-join des contacts et référent
     * (évite le N+1). Si $referentIds est fourni, restreint aux immersions dont le volontaire est
     * rattaché à l'un de ces référents ; un tableau vide ne renvoie rien.
     *
     * @param list<int>|null $referentIds
     *
     * @return list<Appariement>
     */
    public function findForSuivi(Session $session, ?array $referentIds = null): array
    {
        $qb = $this->createQueryBuilder('a')
            ->join('a.participant', 'p')->addSelect('p')
            ->join('p.contact', 'pc')->addSelect('pc')
            ->leftJoin('pc.referent', 'r')->addSelect('r')
            ->join('a.entreprise', 'e')->addSelect('e')
            ->join('e.contact', 'ec')->addSelect('ec')
            ->where('p.session = :session')
            ->orderBy('pc.nomFamille', 'ASC')
            ->addOrderBy('pc.prenom', 'ASC')
            ->addOrderBy('a.dateMatch', 'ASC')
            ->setParameter('session', $session);

        $this->applyReferentFilter($qb, $referentIds, 'pc');

        /** @var list<Appariement> $result */
        $result = $qb->getQuery()->getResult();

        return $result;
    }
}

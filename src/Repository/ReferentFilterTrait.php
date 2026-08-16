<?php

declare(strict_types=1);

namespace App\Repository;

use Doctrine\ORM\QueryBuilder;

/**
 * Filtrage des requêtes de suivi par référent, partagé entre repositories.
 *
 * Sémantique des trois cas :
 *  - null : aucun filtre (admin, voit tout) ;
 *  - []   : aucun résultat (un référent sans référent rattaché ne voit rien) ;
 *  - liste d'ids : restreint aux volontaires rattachés à ces référents.
 */
trait ReferentFilterTrait
{
    /**
     * @param list<int>|null $referentIds
     * @param string         $contactAlias alias DQL de l'entité Contact du volontaire dans $qb
     */
    private function applyReferentFilter(QueryBuilder $qb, ?array $referentIds, string $contactAlias = 'c'): void
    {
        if (null === $referentIds) {
            return;
        }

        if ([] === $referentIds) {
            $qb->andWhere('1 = 0');

            return;
        }

        $qb->andWhere(sprintf('%s.referent IN (:referentIds)', $contactAlias))
            ->setParameter('referentIds', $referentIds);
    }
}

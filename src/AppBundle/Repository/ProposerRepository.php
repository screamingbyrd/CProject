<?php

namespace AppBundle\Repository;

/**
 * ProposerRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class ProposerRepository extends \Doctrine\ORM\EntityRepository
{
    public function countTotalDifferentProposer()
    {
        $query = $this->createQueryBuilder('e')->select("e")
            ->leftJoin('AppBundle:user', 'u', 'WITH', 'e = u.proposer');

        $proposers = $query->getQuery()->getResult();

        return count($proposers);
    }

    public function countActiveBetween($endDate){
        return $this->getEntityManager()
            ->createQuery(
                'select count(distinct em.id) as total
                    from AppBundle:proposer em
                    where em.creationDate <= :endDate'
            )->setParameter('endDate',$endDate)->execute();
    }
}

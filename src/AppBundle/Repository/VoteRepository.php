<?php

namespace AppBundle\Repository;

/**
 * VoteRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class VoteRepository extends \Doctrine\ORM\EntityRepository
{
    public function getRecentVote($voter)
    {
        $query = $this->createQueryBuilder('po');
        $query->andWhere('po.archived = 0 and po.date > :date and po.voter = :voter')
            ->setParameter('date', new \DateTime('-3 month'))
            ->setParameter('voter', $voter);

        $offers = $query->getQuery()->getResult();

        return $offers;
    }

    public function countVoteOffer($offer)
    {
        $query = $this->createQueryBuilder('v');
        $query->andWhere('v.offer = :offer')
            ->setParameter('offer', $offer);

        $offers = $query->getQuery()->getResult();

        return count($offers);
    }
}

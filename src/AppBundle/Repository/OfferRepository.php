<?php

namespace AppBundle\Repository;

/**
 * OfferRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class OfferRepository extends \Doctrine\ORM\EntityRepository
{

    public function getNotificationOffers($notification)
    {
        $query = $this->createQueryBuilder('o');
        $query->andWhere('o.startDate > :date OR (o.creationDate > :date AND o.slot is not null)')
            ->setParameter('date', $notification->getDate());

        if($notification->getTypeNotification() == 'employer'){
            $query->andWhere('o.employer = :employer')
            ->setParameter('employer', $notification->getElementId());
        }elseif ($notification->getTypeNotification() == 'tag'){
            $query->andWhere(':tag MEMBER OF o.tag')
            ->setParameter('tag', $notification->getElementId());
        }

        $offers = $query->getQuery()->getResult();

        return $offers;
    }

    public function getOfferTags($id)
    {
        $query = $this->createQueryBuilder('o')->select('t.name')->distinct();
        $query->innerJoin('o.tag', 't')->andWhere('o.archived  = false and o.employer = :employer')
            ->setParameter('employer', $id)
            ->orderBy('t.name', 'asc');

        $tags = $query->getQuery()->getResult();

        return $tags;
    }
}

<?php

namespace AppBundle\Repository;

/**
 * FeaturedOfferRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class FeaturedOfferRepository extends \Doctrine\ORM\EntityRepository
{

    public function getCurrentFeaturedOffer()
    {
        return $this->getEntityManager()
            ->createQuery(
                'SELECT fo FROM AppBundle:featuredOffer fo WHERE fo.archived = 0 AND  fo.startDate <= CURRENT_TIMESTAMP() AND fo.endDate > CURRENT_TIMESTAMP()'
            )->execute();
    }

}
<?php
/**
 * Created by PhpStorm.
 * User: Altea IT
 * Date: 29/05/2018
 * Time: 10:53
 */

namespace AppBundle\Service;

use AppBundle\Entity\User;

class CreditInfo
{

    private $publishOffer = 1;
    private $boostOffers = 3;
    private $featuredOffer = 11;
    private $featuredProposer = 10;
    private $buySlot = 20;
    private $oneCredit = 234;
    private $fiveCredit = 1111.5;
    private $tenCredit = 2223;

    public function __construct()
    {
    }

    /**
     * @return int
     */
    public function getPublishOffer()
    {
        return $this->publishOffer;
    }

    /**
     * @param int $publishOffer
     * @return CreditInfo
     */
    public function setPublishOffer($publishOffer)
    {
        $this->publishOffer = $publishOffer;
        return $this;
    }

    /**
     * @return int
     */
    public function getBoostOffers()
    {
        return $this->boostOffers;
    }

    /**
     * @param int $boostOffers
     * @return CreditInfo
     */
    public function setBoostOffers($boostOffers)
    {
        $this->boostOffers = $boostOffers;
        return $this;
    }

    /**
     * @return int
     */
    public function getFeaturedOffer()
    {
        return $this->featuredOffer;
    }

    /**
     * @param int $featuredOffer
     * @return CreditInfo
     */
    public function setFeaturedOffer($featuredOffer)
    {
        $this->featuredOffer = $featuredOffer;
        return $this;
    }

    /**
     * @return int
     */
    public function getFeaturedProposer()
    {
        return $this->featuredProposer;
    }

    /**
     * @param int $featuredProposer
     * @return CreditInfo
     */
    public function setFeaturedProposer($featuredProposer)
    {
        $this->featuredProposer = $featuredProposer;
        return $this;
    }

    /**
     * @return int
     */
    public function getBuySlot()
    {
        return $this->buySlot;
    }

    /**
     * @param int $buySlot
     * @return CreditInfo
     */
    public function setBuySlot($buySlot)
    {
        $this->buySlot = $buySlot;
        return $this;
    }

    /**
     * @return int
     */
    public function getOneCredit()
    {
        return $this->oneCredit;
    }

    /**
     * @param int $oneCredit
     * @return CreditInfo
     */
    public function setOneCredit($oneCredit)
    {
        $this->oneCredit = $oneCredit;
        return $this;
    }

    /**
     * @return int
     */
    public function getFiveCredit()
    {
        return $this->fiveCredit;
    }

    /**
     * @param int $fiveCredit
     * @return CreditInfo
     */
    public function setFiveCredit($fiveCredit)
    {
        $this->fiveCredit = $fiveCredit;
        return $this;
    }

    /**
     * @return int
     */
    public function getTenCredit()
    {
        return $this->tenCredit;
    }

    /**
     * @param int $tenCredit
     * @return CreditInfo
     */
    public function setTenCredit($tenCredit)
    {
        $this->tenCredit = $tenCredit;
        return $this;
    }






}
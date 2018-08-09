<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Vote
 *
 * @ORM\Table(name="postulated_offers")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\VoteRepository")
 */
class Vote
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Voter", cascade={"persist"})
     */
    private $voter;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Offer", cascade={"persist"})
     */
    private $offer;

    /**
     * @ORM\Column(name="estimation", type="text")
     */
    private $estimation;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="datetime")
     */
    private $date;

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set voter
     *
     * @param integer $voter
     *
     * @return Vote
     */
    public function setVoter($voter)
    {
        $this->voter = $voter;

        return $this;
    }

    /**
     * Get voter
     *
     * @return int
     */
    public function getVoter()
    {
        return $this->voter;
    }

    /**
     * Set offer
     *
     * @param integer $offer
     *
     * @return Vote
     */
    public function setOffer($offer)
    {
        $this->offer = $offer;

        return $this;
    }

    /**
     * Get offer
     *
     * @return int
     */
    public function getOffer()
    {
        return $this->offer;
    }

    /**
     * Set date
     *
     * @param \DateTime $date
     *
     * @return Vote
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date
     *
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @return mixed
     */
    public function getEstimation()
    {
        return $this->estimation;
    }

    /**
     * @param mixed $estimation
     * @return Vote
     */
    public function setEstimation($estimation)
    {
        $this->estimation = $estimation;
        return $this;
    }

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->date =  new \datetime();
    }
}

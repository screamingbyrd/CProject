<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Offer
 *
 * @ORM\Table(name="offer")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\OfferRepository")
 */
class Offer
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
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Proposer", cascade={"persist"})
     *
     */
    private $proposer;

    /**
     *
     * @ORM\Column(name="fromPrice", type="text", nullable=true)
     */
    private $fromPrice;

    /**
     *
     * @ORM\Column(name="toPrice", type="text", nullable=true)
     */
    private $toPrice;

    /**
     * @var string
     *
     * @ORM\Column(name="location", type="string", length=255)
     */
    private $location;

    /**
     *
     * @ORM\Column(name="description", type="text")
     */
    private $description;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=255)
     */
    private $title;


    /**
     * @var \datetime
     *
     * @ORM\Column(name="creationDate", type="datetime")
     */
    private $creationDate;

    /**
     * @var boolean
     *
     * @ORM\Column(name="archived", type="boolean")

     */
    protected $archived = 0;

    private $offerUrl;

    /**
     * @var boolean
     *
     * @ORM\Column(name="validated", type="boolean", nullable=true)

     */
    protected $validated;

    public function __toString()
    {
        return (string)$this->getTag();
    }

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
     * Constructor
     */
    public function __construct()
    {
        $this->creationDate =  new \Datetime();
    }

    /**
     * @return \datetime
     */
    public function getCreationDate()
    {
        return $this->creationDate;
    }

    /**
     * @param \datetime $creationDate
     * @return Offer
     */
    public function setCreationDate($creationDate)
    {
        $this->creationDate = $creationDate;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isArchived()
    {
        return $this->archived;
    }

    /**
     * @param boolean $archived
     * @return Offer
     */
    public function setArchived($archived)
    {
        $this->archived = $archived;
        return $this;
    }

    /**
     * @return string
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * @param string $location
     * @return Offer
     */
    public function setLocation($location)
    {
        $this->location = $location;
        return $this;
    }

    /**
     * Set proposer
     *
     * @param \AppBundle\Entity\Proposer $proposer
     *
     * @return Offer
     */
    public function setProposer(\AppBundle\Entity\Proposer $proposer = null)
    {
        $this->proposer = $proposer;

        return $this;
    }

    /**
     * Get user
     *
     * @return \AppBundle\Entity\Proposer
     */
    public function getProposer()
    {
        return $this->proposer;
    }


    /**
     * @return mixed
     */
    public function getOfferUrl()
    {
        return $this->offerUrl;
    }

    /**
     * @param mixed $slot
     * @return string
     */
    public function setOfferUrl($offerUrl)
    {
        $this->offerUrl = $offerUrl;
        return $this;
    }


    /**
     * @return bool
     */
    public function isValidated()
    {
        return $this->validated;
    }

    /**
     * @param bool $validated
     * @return Offer
     */
    public function setValidated($validated)
    {
        $this->validated = $validated;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param mixed $description
     * @return Offer
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     * @return Offer
     */
    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getFromPrice()
    {
        return $this->fromPrice;
    }

    /**
     * @param mixed $fromPrice
     * @return Offer
     */
    public function setFromPrice($fromPrice)
    {
        $this->fromPrice = $fromPrice;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getToPrice()
    {
        return $this->toPrice;
    }

    /**
     * @param mixed $toPrice
     * @return Offer
     */
    public function setToPrice($toPrice)
    {
        $this->toPrice = $toPrice;
        return $this;
    }


}

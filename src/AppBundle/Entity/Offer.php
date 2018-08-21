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
     *
     * @ORM\Column(name="finalPrice", type="text", nullable=true)
     */
    private $finalPrice;

    /**
     * @var string
     *
     * @ORM\Column(name="zipcode", type="string", length=255)
     */
    private $zipcode;

    /**
     * @var string
     *
     * @ORM\Column(name="town", type="string", length=255)
     */
    private $town;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=255)
     */
    private $type;

    /**
     * @var int
     *
     * @ORM\Column(name="surface", type="integer")
     */
    private $surface;

    /**
     * @var int
     *
     * @ORM\Column(name="groundSurface", type="integer", nullable=true)
     */
    private $groundSurface;

    /**
     * @var int
     *
     * @ORM\Column(name="roomNumber", type="integer")
     */
    private $roomNumber;

    /**
     * @var int
     *
     * @ORM\Column(name="bathroomNumber", type="integer")
     */
    private $bathroomNumber;

    /**
     * @var int
     *
     * @ORM\Column(name="totalFloor", type="integer", nullable=true)
     */
    private $totalFloor;

    /**
     * @var int
     *
     * @ORM\Column(name="floor", type="integer", nullable=true)
     */
    private $floor;

    /**
     * @var int
     *
     * @ORM\Column(name="basementSurface", type="integer", nullable=true)
     */
    private $basementSurface;

    /**
     * @var int
     *
     * @ORM\Column(name="parkingNumber", type="integer", nullable=true)
     */
    private $parkingNumber;

    /**
     * @var int
     *
     * @ORM\Column(name="buildingYear", type="integer", nullable=true)
     */
    private $buildingYear;

    /**
     * @var boolean
     *
     * @ORM\Column(name="lift", type="boolean")

     */
    protected $lift;

    /**
     * @var boolean
     *
     * @ORM\Column(name="balcony", type="boolean")

     */
    protected $balcony;

    /**
     * @var string
     *
     * @ORM\Column(name="location", type="string", length=255)
     */
    private $location;


    /**
     * @var \datetime
     *
     * @ORM\Column(name="creationDate", type="datetime")
     */
    private $creationDate;

    /**
     * @var \datetime
     *
     * @ORM\Column(name="activationDate", type="datetime")
     */
    private $activationDate;

    /**
     * @var boolean
     *
     * @ORM\Column(name="archived", type="boolean")

     */
    protected $archived = 0;

    private $offerUrl;

    private $countVote;

    private $remainingDays;

    /**
     * @var boolean
     *
     * @ORM\Column(name="validated", type="boolean", nullable=true)

     */
    protected $validated;

    /**
     *
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Image", mappedBy="offer", cascade={"persist"})
     *
     */
    private $images;

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
        $this->images = new \Doctrine\Common\Collections\ArrayCollection();
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

    /**
     * @return string
     */
    public function getZipcode()
    {
        return $this->zipcode;
    }

    /**
     * @param string $zipcode
     * @return Offer
     */
    public function setZipcode($zipcode)
    {
        $this->zipcode = $zipcode;
        return $this;
    }

    /**
     * @return string
     */
    public function getTown()
    {
        return $this->town;
    }

    /**
     * @param string $town
     * @return Offer
     */
    public function setTown($town)
    {
        $this->town = $town;
        return $this;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     * @return Offer
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return int
     */
    public function getSurface()
    {
        return $this->surface;
    }

    /**
     * @param int $surface
     * @return Offer
     */
    public function setSurface($surface)
    {
        $this->surface = $surface;
        return $this;
    }

    /**
     * @return int
     */
    public function getGroundSurface()
    {
        return $this->groundSurface;
    }

    /**
     * @param int $groundSurface
     * @return Offer
     */
    public function setGroundSurface($groundSurface)
    {
        $this->groundSurface = $groundSurface;
        return $this;
    }

    /**
     * @return int
     */
    public function getRoomNumber()
    {
        return $this->roomNumber;
    }

    /**
     * @param int $roomNumber
     * @return Offer
     */
    public function setRoomNumber($roomNumber)
    {
        $this->roomNumber = $roomNumber;
        return $this;
    }

    /**
     * @return int
     */
    public function getBathroomNumber()
    {
        return $this->bathroomNumber;
    }

    /**
     * @param int $bathroomNumber
     * @return Offer
     */
    public function setBathroomNumber($bathroomNumber)
    {
        $this->bathroomNumber = $bathroomNumber;
        return $this;
    }

    /**
     * @return int
     */
    public function getTotalFloor()
    {
        return $this->totalFloor;
    }

    /**
     * @param int $totalFloor
     * @return Offer
     */
    public function setTotalFloor($totalFloor)
    {
        $this->totalFloor = $totalFloor;
        return $this;
    }

    /**
     * @return int
     */
    public function getFloor()
    {
        return $this->floor;
    }

    /**
     * @param int $floor
     * @return Offer
     */
    public function setFloor($floor)
    {
        $this->floor = $floor;
        return $this;
    }

    /**
     * @return int
     */
    public function getBasementSurface()
    {
        return $this->basementSurface;
    }

    /**
     * @param int $basementSurface
     * @return Offer
     */
    public function setBasementSurface($basementSurface)
    {
        $this->basementSurface = $basementSurface;
        return $this;
    }

    /**
     * @return int
     */
    public function getParkingNumber()
    {
        return $this->parkingNumber;
    }

    /**
     * @param int $parkingNumber
     * @return Offer
     */
    public function setParkingNumber($parkingNumber)
    {
        $this->parkingNumber = $parkingNumber;
        return $this;
    }

    /**
     * @return int
     */
    public function getBuildingYear()
    {
        return $this->buildingYear;
    }

    /**
     * @param int $buildingYear
     * @return Offer
     */
    public function setBuildingYear($buildingYear)
    {
        $this->buildingYear = $buildingYear;
        return $this;
    }

    /**
     * @return bool
     */
    public function isLift()
    {
        return $this->lift;
    }

    /**
     * @param bool $lift
     * @return Offer
     */
    public function setLift($lift)
    {
        $this->lift = $lift;
        return $this;
    }

    /**
     * @return bool
     */
    public function isBalcony()
    {
        return $this->balcony;
    }

    /**
     * @param bool $balcony
     * @return Offer
     */
    public function setBalcony($balcony)
    {
        $this->balcony = $balcony;
        return $this;
    }

    /**
     * @return \datetime
     */
    public function getActivationDate()
    {
        return $this->activationDate;
    }

    /**
     * @param \datetime $activationDate
     * @return Offer
     */
    public function setActivationDate($activationDate)
    {
        $this->activationDate = $activationDate;
        return $this;
    }





    /**
     * Get lift
     *
     * @return boolean
     */
    public function getLift()
    {
        return $this->lift;
    }

    /**
     * Get balcony
     *
     * @return boolean
     */
    public function getBalcony()
    {
        return $this->balcony;
    }

    /**
     * Get archived
     *
     * @return boolean
     */
    public function getArchived()
    {
        return $this->archived;
    }

    /**
     * Get validated
     *
     * @return boolean
     */
    public function getValidated()
    {
        return $this->validated;
    }

    /**
     * Add image
     *
     * @param \AppBundle\Entity\Image $image
     *
     * @return Offer
     */
    public function addImage(\AppBundle\Entity\Image $image)
    {
        $this->images[] = $image;

        return $this;
    }

    /**
     * Remove image
     *
     * @param \AppBundle\Entity\Image $image
     */
    public function removeImage(\AppBundle\Entity\Image $image)
    {
        $this->images->removeElement($image);
    }

    /**
     * Get images
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getImages()
    {
        return $this->images;
    }

    /**
     * @return mixed
     */
    public function getFinalPrice()
    {
        return $this->finalPrice;
    }

    /**
     * @param mixed $finalPrice
     * @return Offer
     */
    public function setFinalPrice($finalPrice)
    {
        $this->finalPrice = $finalPrice;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCountVote()
    {
        return $this->countVote;
    }

    /**
     * @param mixed $countVote
     * @return Offer
     */
    public function setCountVote($countVote)
    {
        $this->countVote = $countVote;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getRemainingDays()
    {
        return $this->remainingDays;
    }

    /**
     * @param mixed $remainingDays
     * @return Offer
     */
    public function setRemainingDays($remainingDays)
    {
        $this->remainingDays = $remainingDays;
        return $this;
    }


}

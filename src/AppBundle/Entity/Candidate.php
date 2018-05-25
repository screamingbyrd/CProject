<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Candidate
 *
 * @ORM\Table(name="candidate")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\CandidateRepository")
 */
class Candidate
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
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\User", cascade={"persist"})
     */
    private $user;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="string", length=255)
     */
    private $description;

    /**
     * @var int
     *
     * @ORM\Column(name="age", type="integer")
     */
    private $age;

    /**
     * @var string
     *
     * @ORM\Column(name="experience", type="string", length=255)
     */
    private $experience;

    /**
     * @var string
     *
     * @ORM\Column(name="license", type="string", length=255)
     */
    private $license;

    /**
     * @var string
     *
     * @ORM\Column(name="diploma", type="string", length=255)
     */
    private $diploma;

    /**
     * @var string
     *
     * @ORM\Column(name="socialMedia", type="string", length=255)
     */
    private $socialMedia;

    /**
     * @var string
     *
     * @ORM\Column(name="phone", type="string", length=255)
     */
    private $phone;

    /**
     * @var int
     *
     * @ORM\Column(name="searchedTag", type="integer")
     */
    private $searchedTag;

    /**
     * @ORM\ManyToMany(targetEntity="AI\AppBundle\Entity\ContractType", cascade={"persist"})
     */
    private $typeContract;

    /**
     * @ORM\ManyToMany(targetEntity="AI\AppBundle\Entity\Tag", cascade={"persist"})
     */
    private $tag;

    /**
     * @ORM\ManyToMany(targetEntity="AI\AppBundle\Entity\Tag", cascade={"persist"})
     */
    private $notification;

    /**
     * @ORM\ManyToMany(targetEntity="AI\AppBundle\Entity\Offer", cascade={"persist"})
     */
    private $favorite;




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
     * Set name
     *
     * @param string $name
     *
     * @return Candidate
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return Candidate
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set age
     *
     * @param integer $age
     *
     * @return Candidate
     */
    public function setAge($age)
    {
        $this->age = $age;

        return $this;
    }

    /**
     * Get age
     *
     * @return int
     */
    public function getAge()
    {
        return $this->age;
    }

    /**
     * Set experience
     *
     * @param string $experience
     *
     * @return Candidate
     */
    public function setExperience($experience)
    {
        $this->experience = $experience;

        return $this;
    }

    /**
     * Get experience
     *
     * @return string
     */
    public function getExperience()
    {
        return $this->experience;
    }

    /**
     * Set license
     *
     * @param string $license
     *
     * @return Candidate
     */
    public function setLicense($license)
    {
        $this->license = $license;

        return $this;
    }

    /**
     * Get license
     *
     * @return string
     */
    public function getLicense()
    {
        return $this->license;
    }

    /**
     * Set diploma
     *
     * @param string $diploma
     *
     * @return Candidate
     */
    public function setDiploma($diploma)
    {
        $this->diploma = $diploma;

        return $this;
    }

    /**
     * Get diploma
     *
     * @return string
     */
    public function getDiploma()
    {
        return $this->diploma;
    }

    /**
     * Set socialMedia
     *
     * @param string $socialMedia
     *
     * @return Candidate
     */
    public function setSocialMedia($socialMedia)
    {
        $this->socialMedia = $socialMedia;

        return $this;
    }

    /**
     * Get socialMedia
     *
     * @return string
     */
    public function getSocialMedia()
    {
        return $this->socialMedia;
    }

    /**
     * Set phone
     *
     * @param string $phone
     *
     * @return Candidate
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * Get phone
     *
     * @return string
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * Set searchedTag
     *
     * @param integer $searchedTag
     *
     * @return Candidate
     */
    public function setSearchedTag($searchedTag)
    {
        $this->searchedTag = $searchedTag;

        return $this;
    }

    /**
     * Get searchedTag
     *
     * @return int
     */
    public function getSearchedTag()
    {
        return $this->searchedTag;
    }

    /**
     * Set typeContract
     *
     * @param integer $typeContract
     *
     * @return Candidate
     */
    public function setTypeContract($typeContract)
    {
        $this->typeContract = $typeContract;

        return $this;
    }

    /**
     * Get typeContract
     *
     * @return int
     */
    public function getTypeContract()
    {
        return $this->typeContract;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->typeContract = new \Doctrine\Common\Collections\ArrayCollection();
        $this->tag = new \Doctrine\Common\Collections\ArrayCollection();
        $this->notification = new \Doctrine\Common\Collections\ArrayCollection();
        $this->favorite = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Set user
     *
     * @param \AI\AppBundle\Entity\User $user
     *
     * @return Candidate
     */
    public function setUser(\AI\AppBundle\Entity\User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \AI\AppBundle\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Add typeContract
     *
     * @param \AI\AppBundle\Entity\ContractType $typeContract
     *
     * @return Candidate
     */
    public function addTypeContract(\AI\AppBundle\Entity\ContractType $typeContract)
    {
        $this->typeContract[] = $typeContract;

        return $this;
    }

    /**
     * Remove typeContract
     *
     * @param \AI\AppBundle\Entity\ContractType $typeContract
     */
    public function removeTypeContract(\AI\AppBundle\Entity\ContractType $typeContract)
    {
        $this->typeContract->removeElement($typeContract);
    }

    /**
     * Add tag
     *
     * @param \AI\AppBundle\Entity\Tag $tag
     *
     * @return Candidate
     */
    public function addTag(\AI\AppBundle\Entity\Tag $tag)
    {
        $this->tag[] = $tag;

        return $this;
    }

    /**
     * Remove tag
     *
     * @param \AI\AppBundle\Entity\Tag $tag
     */
    public function removeTag(\AI\AppBundle\Entity\Tag $tag)
    {
        $this->tag->removeElement($tag);
    }

    /**
     * Get tag
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getTag()
    {
        return $this->tag;
    }

    /**
     * Add notification
     *
     * @param \AI\AppBundle\Entity\Tag $notification
     *
     * @return Candidate
     */
    public function addNotification(\AI\AppBundle\Entity\Tag $notification)
    {
        $this->notification[] = $notification;

        return $this;
    }

    /**
     * Remove notification
     *
     * @param \AI\AppBundle\Entity\Tag $notification
     */
    public function removeNotification(\AI\AppBundle\Entity\Tag $notification)
    {
        $this->notification->removeElement($notification);
    }

    /**
     * Get notification
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getNotification()
    {
        return $this->notification;
    }

    /**
     * Add favorite
     *
     * @param \AI\AppBundle\Entity\Offer $favorite
     *
     * @return Candidate
     */
    public function addFavorite(\AI\AppBundle\Entity\Offer $favorite)
    {
        $this->favorite[] = $favorite;

        return $this;
    }

    /**
     * Remove favorite
     *
     * @param \AI\AppBundle\Entity\Offer $favorite
     */
    public function removeFavorite(\AI\AppBundle\Entity\Offer $favorite)
    {
        $this->favorite->removeElement($favorite);
    }

    /**
     * Get favorite
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getFavorite()
    {
        return $this->favorite;
    }
}
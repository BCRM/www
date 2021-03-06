<?php

namespace BCRM\BackendBundle\Entity\Event;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use LiteCQRS\Plugin\CRUD\AggregateResource;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * Event
 *
 * @ORM\Table(name="event")
 * @ORM\Entity(repositoryClass="BCRM\BackendBundle\Entity\Event\DoctrineEventRepository")
 */
class Event extends AggregateResource
{
    /**
     * @var integer
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     * @Assert\NotBlank()
     * @ORM\Column(type="text")
     */
    protected $name;

    /**
     * @var integer
     * @Assert\NotBlank()
     * @Assert\Type(type="integer")
     * @Assert\Range(min=1)
     * @ORM\Column(type="integer")
     */
    protected $capacity;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime")
     * @Assert\NotBlank()
     * @Assert\Type(type="\DateTime")
     */
    protected $start;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", name="registration_start")
     * @Assert\NotBlank()
     * @Assert\Type(type="\DateTime")
     */
    protected $registrationStart;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", name="registration_end")
     * @Assert\NotBlank()
     * @Assert\Type(type="\DateTime")
     */
    protected $registrationEnd;

    /**
     * Price for aticket in cents (including VAT)
     *
     * @var integer
     * @Assert\NotBlank()
     * @Assert\Type(type="integer")
     * @Assert\Range(min=1)
     * @ORM\Column(type="integer")
     */
    protected $price;

    /**
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime")
     * @var \DateTime
     */
    protected $created;

    /**
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(type="datetime", nullable=true)
     * @var \DateTime
     */
    protected $updated;

    /**
     * @ORM\OneToMany(targetEntity="BCRM\BackendBundle\Entity\Event\Registration", mappedBy="event")
     * @var Registration[]
     */
    protected $registrations;

    /**
     * @ORM\OneToMany(targetEntity="BCRM\BackendBundle\Entity\Event\Unregistration", mappedBy="event")
     * @var Unregistration[]
     */
    protected $unregistrations;

    /**
     * @ORM\OneToMany(targetEntity="BCRM\BackendBundle\Entity\Event\Ticket", mappedBy="event")
     * @var Ticket[]
     */
    protected $tickets;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getCapacity()
    {
        return $this->capacity;
    }

    /**
     * @param int $capacity
     */
    public function setCapacity($capacity)
    {
        $this->capacity = (int)$capacity;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return int
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @param int $price
     */
    public function setPrice($price)
    {
        $this->price = (int)$price;
    }

    /**
     * @return \DateTime
     */
    public function getRegistrationStart()
    {
        return $this->registrationStart;
    }

    /**
     * @param \DateTime $registrationStart
     */
    public function setRegistrationStart(\DateTime $registrationStart)
    {
        $this->registrationStart = $registrationStart;
    }

    /**
     * @return \DateTime
     */
    public function getStart()
    {
        return $this->start;
    }

    /**
     * @param \DateTime $start
     */
    public function setStart(\DateTime $start)
    {
        $this->start = $start;
    }

    public function __toString()
    {
        $str = $this->start->format('Y-m-d');
        return $str;
    }

    /**
     * @param \DateTime $registrationEnd
     */
    public function setRegistrationEnd(\DateTime $registrationEnd)
    {
        $this->registrationEnd = $registrationEnd;
    }

    /**
     * @return \DateTime
     */
    public function getRegistrationEnd()
    {
        return $this->registrationEnd;
    }
}


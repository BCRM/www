<?php

namespace BCRM\BackendBundle\Entity\Event;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use LiteCQRS\Plugin\CRUD\AggregateResource;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * Ticket
 *
 * @ORM\Table(name="ticket")
 * @ORM\Entity(repositoryClass="BCRM\BackendBundle\Entity\Event\DoctrineTicketRepository")
 */
class Ticket extends AggregateResource
{
    const DAY_SATURDAY = 1;

    const DAY_SUNDAY = 2;

    /**
     * @var integer
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @Assert\NotBlank()
     * @ORM\ManyToOne(targetEntity="BCRM\BackendBundle\Entity\Event\Event", inversedBy="tickets")
     * @ORM\JoinColumn(name="event_id", referencedColumnName="id", nullable=false)
     * @var Event
     */
    protected $event;

    /**
     * @var string
     * @Assert\NotBlank()
     * @ORM\Column(type="text")
     */
    protected $name;

    /**
     * @var string
     * @Assert\NotBlank()
     * @Assert\Email()
     * @ORM\Column(type="text")
     */
    protected $email;

    /**
     * @var integer
     * @Assert\NotBlank()
     * @Assert\Type(type="integer")
     * @Assert\Range(min=1,max=2)
     * @ORM\Column(type="integer")
     */
    protected $day;

    /**
     * @ORM\Column(type="string", nullable=false, name="code")
     * @var string Ticket code
     */
    protected $code;

    /**
     * @var boolean
     * @Assert\NotBlank()
     * @Assert\Type(type="boolean")
     * @ORM\Column(type="boolean")
     */
    protected $notified = 0;

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
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        $str  = $this->email;
        $days = array();
        if ($this->day === static::DAY_SATURDAY) {
            $days[] = 'SA';
        } else {
            $days[] = 'SU';
        }
        $str .= ' (' . join('+', $days) . ')';
        return $str;
    }

    /**
     * @return bool
     */
    public function isSaturday()
    {
        return $this->getDay() === static::DAY_SATURDAY;
    }

    /**
     * @return int
     */
    public function getDay()
    {
        return $this->day;
    }

    /**
     * @return bool
     */
    public function isSunday()
    {
        return $this->getDay() === static::DAY_SUNDAY;
    }

    /**
     * @return \BCRM\BackendBundle\Entity\Event\Event
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param string $code
     */
    public function setCode($code)
    {
        $this->code = $code;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
    
}



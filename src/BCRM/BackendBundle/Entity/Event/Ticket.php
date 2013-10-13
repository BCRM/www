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
     * @ORM\ManyToOne(targetEntity="BCRM\BackendBundle\Entity\Event\Event", inversedBy="registrations")
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
    
    public function __toString()
    {
        $str = $this->email;
        if ($this->day === static::DAY_SATURDAY) {
            $str .= ' Samstag';
        } else {
            $str .= ' Sonntag';
        }
        return  $str;
    }

    /**
     * @return int
     */
    public function getDay()
    {
        return $this->day;
    }
}


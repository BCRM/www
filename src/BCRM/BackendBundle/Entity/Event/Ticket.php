<?php

namespace BCRM\BackendBundle\Entity\Event;

use BCRM\BackendBundle\Entity\Payment;
use BCRM\BackendBundle\Exception\InvalidArgumentException;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use LiteCQRS\Plugin\CRUD\AggregateResource;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * Ticket
 *
 * @ORM\Table(name="ticket",indexes={@ORM\Index(name="email_idx", columns={"email"})}, uniqueConstraints={@ORM\UniqueConstraint(name="event_email_day",columns={"event_id", "email", "day"})})
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
     * @ORM\Column(type="string")
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
     * @Assert\NotBlank()
     * @ORM\ManyToOne(targetEntity="BCRM\BackendBundle\Entity\Payment")
     * @ORM\JoinColumn(name="payment_id", referencedColumnName="id", nullable=true)
     * @var Payment
     */
    protected $payment;

    /**
     * @var boolean
     * @Assert\NotBlank()
     * @Assert\Type(type="boolean")
     * @ORM\Column(type="boolean")
     */
    protected $notified = 0;

    /**
     * @var boolean
     * @ORM\Column(type="datetime", name="checked_in", nullable=true)
     * @var \DateTime
     * @Assert\Type(type="\DateTime")
     */
    protected $checkedIn;

    /**
     * @var boolean
     * @Assert\Type(type="boolean")
     * @ORM\Column(type="boolean")
     */
    protected $printed = 0;

    /**
     * @var integer
     * @Assert\NotBlank()
     * @Assert\Type(type="integer")
     * @ORM\Column(type="integer")
     */
    protected $type = Registration::TYPE_NORMAL;

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
     * @param string $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
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
     * @param int $day
     */
    public function setDay($day)
    {
        if (!in_array($day, array(static::DAY_SATURDAY, static::DAY_SUNDAY))) {
            throw new InvalidArgumentException(sprintf('Invalid day: %d', $day));
        }
        $this->day = $day;
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
     * @param \BCRM\BackendBundle\Entity\Event\Event $event
     */
    public function setEvent(Event $event)
    {
        $this->event = $event;
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

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    public function isCheckedIn()
    {
        return $this->checkedIn !== null;
    }

    /**
     * @return \DateTime|null
     */
    public function getCheckinTime()
    {
        return $this->checkedIn;
    }

    /**
     * @param boolean $checkedIn
     */
    public function setCheckedIn($checkedIn)
    {
        $this->checkedIn = $checkedIn ? new \DateTime() : null;
    }

    /**
     * @param int $type
     */
    public function setType($type)
    {
        if (!in_array($type, array(Registration::TYPE_NORMAL, Registration::TYPE_VIP, Registration::TYPE_SPONSOR))) {
            throw new \InvalidArgumentException("Invalid type");
        }
        $this->type = $type;
    }

    /**
     * @return int
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        switch ($this->getType()) {
            case Registration::TYPE_VIP:
                return 'VIP';
            case Registration::TYPE_SPONSOR:
                return 'Sponsor';
        }
        return '';
    }

    /**
     * @param boolean $notified
     */
    public function setNotified($notified)
    {
        $this->notified = (bool)$notified;
    }

    /**
     * @return bool
     */
    public function isNotified()
    {
        return (bool)$this->notified;
    }

    /**
     * @return bool
     */
    public function isPrinted()
    {
        return $this->printed;
    }

    /**
     * @return boolean
     */
    public function isPaid()
    {
        return $this->payment !== null;
    }

    /**
     * @param Payment $payment
     */
    public function setPayment(Payment $payment)
    {
        $this->payment = $payment;
    }

    /**
     * @return Payment
     */
    public function getPayment()
    {
        return $this->payment;
    }
}



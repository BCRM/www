<?php

namespace BCRM\BackendBundle\Entity\Event;

use BCRM\BackendBundle\Entity\Payment;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use LiteCQRS\Plugin\CRUD\AggregateResource;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * Registration
 *
 * @ORM\Table(name="registration",indexes={@ORM\Index(name="email_idx", columns={"email"})})})
 * @ORM\Entity(repositoryClass="BCRM\BackendBundle\Entity\Event\DoctrineRegistrationRepository")
 */
class Registration extends AggregateResource
{
    const FOOD_VEGAN = 'vegan';

    const FOOD_VEGETARIAN = 'vegetarian';

    const FOOD_DEFAULT = 'default';

    const TYPE_NORMAL = 1;

    const TYPE_VIP = 2;

    const TYPE_SPONSOR = 3;

    /**
     * @var integer
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=false)
     */
    protected $uuid;

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
     * @ORM\Column(type="string", nullable=true)
     * @Assert\Regex("/^@[a-zA-Z0-9_]{1,15}$/")
     */
    protected $twitter;

    /**
     * @var string
     * @Assert\Regex(pattern="/^#[^\s]{1,25}( #[^\s]{1,25}){0,2}$/")
     * @ORM\Column(type="text", nullable=true)
     */
    protected $tags;

    /**
     * @var string
     * @Assert\NotBlank()
     * @Assert\Email()
     * @ORM\Column(type="string")
     */
    protected $email;

    /**
     * @var boolean
     * @Assert\Type(type="boolean")
     * @ORM\Column(type="boolean")
     */
    protected $saturday = false;

    /**
     * @var boolean
     * @Assert\Type(type="boolean")
     * @ORM\Column(type="boolean")
     */
    protected $sunday = false;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    protected $food;

    /**
     * @var boolean
     * @Assert\Type(type="boolean")
     * @ORM\Column(type="boolean")
     */
    protected $participantList = false;

    /**
     * @var integer
     * @Assert\NotBlank()
     * @Assert\Type(type="integer")
     * @ORM\Column(type="integer")
     */
    protected $type = self::TYPE_NORMAL;

    /**
     * @var int Donation
     * @Assert\Range(min=0)
     * @ORM\Column(type="integer", nullable=false)
     */
    protected $donation = 0;

    /**
     * @var string Payment method
     * @ORM\Column(name="payment_method", type="string", nullable=true)
     * @Assert\Choice(choices={"barzahlen.de", "paypal"})
     */
    protected $paymentMethod;

    /**
     * @ORM\ManyToOne(targetEntity="BCRM\BackendBundle\Entity\Payment")
     * @ORM\JoinColumn(name="payment_id", referencedColumnName="id", nullable=true)
     * @var Payment
     */
    protected $payment;

    /**
     * @ORM\Column(name="payment_notified", type="datetime", nullable=true)
     * @var \DateTime
     */
    protected $paymentNotified;

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
     * @param string $uuid
     */
    public function setUuid($uuid)
    {
        $this->uuid = $uuid;
    }

    /**
     * @return string
     */
    public function getUuid()
    {
        return $this->uuid;
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
        $this->email = strtolower($email);
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
     * @return string
     */
    public function __toString()
    {
        $str  = $this->email;
        $days = array();
        if ($this->saturday) {
            $days[] = 'SA';
        }
        if ($this->sunday) {
            $days[] = 'SU';
        }
        $str .= ' (' . join('+', $days) . ')';
        return $str;
    }

    /**
     * @return boolean
     */
    public function getSunday()
    {
        return $this->sunday;
    }

    /**
     * @param boolean $sunday
     */
    public function setSunday($sunday)
    {
        $this->sunday = (bool)$sunday;
    }

    /**
     * @return boolean
     */
    public function getSaturday()
    {
        return $this->saturday;
    }

    /**
     * @param boolean $saturday
     */
    public function setSaturday($saturday)
    {
        $this->saturday = (bool)$saturday;
    }

    /**
     * @return string
     */
    public function getFood()
    {
        return $this->food;
    }

    /**
     * @param string $food
     */
    public function setFood($food)
    {
        if (!in_array($food, array(self::FOOD_VEGAN, self::FOOD_VEGETARIAN, self::FOOD_DEFAULT))) {
            throw new \InvalidArgumentException("Invalid food");
        }
        $this->food = $food;
    }

    /**
     * @param \BCRM\BackendBundle\Entity\Event\Event $event
     */
    public function setEvent(Event $event)
    {
        $this->event = $event;
    }

    /**
     * @return Event
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * @return string
     */
    public function getTags()
    {
        return $this->tags;
    }

    /**
     * @param string $tags
     */
    public function setTags($tags)
    {
        $this->tags = $tags;
    }

    /**
     * @param int $type
     */
    public function setType($type)
    {
        if (!in_array($type, array(self::TYPE_NORMAL, self::TYPE_VIP, self::TYPE_SPONSOR))) {
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
     * @return string|null
     */
    public function getTwitter()
    {
        return $this->twitter;
    }

    /**
     * @param string|null $twitter
     */
    public function setTwitter($twitter = null)
    {
        $this->twitter = $twitter;
    }

    /**
     * @return boolean
     */
    public function isParticipantList()
    {
        return $this->participantList;
    }

    /**
     * @param boolean $participantList
     */
    public function setParticipantList($participantList)
    {
        $this->participantList = (bool)$participantList;
    }

    /**
     * @return mixed
     */
    public function getPaymentMethod()
    {
        return $this->paymentMethod;
    }

    /**
     * @param mixed $paymentMethod
     */
    public function setPaymentMethod($paymentMethod)
    {
        $this->paymentMethod = $paymentMethod;
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

    /**
     * @return \DateTime
     */
    public function getPaymentNotified()
    {
        return $this->paymentNotified;
    }

    /**
     * @return bool
     */
    public function isPaymentNotified()
    {
        return $this->paymentNotified !== null;
    }

    /**
     * @param \DateTime $paymentNotified
     */
    public function setPaymentNotified($paymentNotified)
    {
        $this->paymentNotified = $paymentNotified;
    }

    /**
     * @return int
     */
    public function getDonation()
    {
        return $this->donation;
    }

    /**
     * @param int $donation
     */
    public function setDonation($donation)
    {
        $this->donation = $donation;
    }
}

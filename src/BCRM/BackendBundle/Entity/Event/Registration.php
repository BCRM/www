<?php

namespace BCRM\BackendBundle\Entity\Event;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use LiteCQRS\Plugin\CRUD\AggregateResource;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * Registration
 *
 * @ORM\Table(name="registration",indexes={@ORM\Index(name="email_idx", columns={"email"})})
 * @ORM\Entity(repositoryClass="BCRM\BackendBundle\Entity\Event\DoctrineRegistrationRepository")
 */
class Registration extends AggregateResource
{
    const ARRIVAL_PUBLIC = 'public';

    const ARRIVAL_PRIVATE = 'private';

    const FOOD_VEGAN = 'vegan';

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
     * @var string Arrival
     * @ORM\Column(type="string", nullable=true)
     * @Assert\Regex("/^@[a-zA-Z0-9_]{1,15}$/")
     */
    protected $twitter;

    /**
     * @var string
     * @Assert\Regex(pattern="/^#[^\s]{1,15}( #[^\s]{1,15}){0,2}$/")
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
     * @ORM\Column(type="string", nullable=true, name="confirmation_key")
     * @var string Login-Key
     */
    protected $confirmationKey;

    /**
     * @var string Arrival
     * @ORM\Column(type="string", nullable=true)
     */
    protected $arrival;

    /**
     * @var string Arrival
     * @ORM\Column(type="string", nullable=true)
     */
    protected $food;

    /**
     * @var boolean
     * @Assert\Type(type="boolean")
     * @ORM\Column(type="boolean")
     */
    protected $confirmed = false;

    /**
     * @var integer
     * @Assert\NotBlank()
     * @Assert\Type(type="integer")
     * @ORM\Column(type="integer")
     */
    protected $type = self::TYPE_NORMAL;

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
        $this->email = strtolower($email);
    }

    /**
     * @return string
     */
    public function getConfirmationKey()
    {
        return $this->confirmationKey;
    }

    /**
     * @param string $confirmationKey
     */
    public function setConfirmationKey($confirmationKey)
    {
        $this->confirmationKey = $confirmationKey;
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
    public function getArrival()
    {
        return $this->arrival;
    }

    /**
     * @param string $arrival
     */
    public function setArrival($arrival)
    {
        if (!in_array($arrival, array(self::ARRIVAL_PRIVATE, self::ARRIVAL_PUBLIC))) {
            throw new \InvalidArgumentException("Invalid arrival");
        }
        $this->arrival = $arrival;
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
        if (!in_array($food, array(self::FOOD_VEGAN, self::FOOD_DEFAULT))) {
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
     * @param boolean $confirmed
     */
    public function setConfirmed($confirmed)
    {
        $this->confirmed = (bool)$confirmed;
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
     * @return bool
     */
    public function isConfirmed()
    {
        return $this->confirmed;
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
}

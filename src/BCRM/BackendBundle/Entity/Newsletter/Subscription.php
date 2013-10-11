<?php

namespace BCRM\BackendBundle\Entity\Newsletter;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use LiteCQRS\Plugin\CRUD\AggregateResource;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * Subscription
 *
 * @ORM\Table(name="subscription")
 * @ORM\Entity(repositoryClass="BCRM\BackendBundle\Entity\Newsletter\DoctrineSubscriptionRepository")
 */
class Subscription extends AggregateResource
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=255)
     * @Assert\NotBlank()
     * @Assert\Email()
     */
    protected $email;

    /**
     * @ORM\Column(type="boolean", name="future_barcamps")
     * @Assert\Type(type="boolean")
     * @var boolean
     */
    protected $futureBarcamps = false;

    /**
     * @ORM\Column(type="boolean")
     * @Assert\Type(type="boolean")
     * @var boolean
     */
    protected $confirmed = false;

    /**
     * @ORM\Column(type="string", nullable=true, name="confirmation_key", length=40)
     * @var string Confirmation Key
     * @Assert\Length(min=40, max=40)
     */
    protected $confirmationKey;

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
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set email
     *
     * @param string $email
     *
     * @return Subscription
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getConfirmed()
    {
        return $this->confirmed;
    }

    /**
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * @return \DateTime
     */
    public function getUpdated()
    {
        return $this->updated;
    }

    /**
     * @return boolean
     */
    public function getFutureBarcamps()
    {
        return $this->futureBarcamps;
    }

    /**
     * @param boolean $futureBarcamps
     */
    public function setFutureBarcamps($futureBarcamps)
    {
        $this->futureBarcamps = (boolean)$futureBarcamps;
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

}


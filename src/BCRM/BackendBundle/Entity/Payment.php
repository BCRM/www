<?php

namespace BCRM\BackendBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use LiteCQRS\Plugin\CRUD\AggregateResource;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Payment Transaction
 *
 * @ORM\Table(name="payment",uniqueConstraints={@ORM\UniqueConstraint(name="payment_txId",columns={"txId"})})
 * @ORM\Entity(repositoryClass="BCRM\BackendBundle\Entity\DoctrinePaymentRepository")
 */
class Payment extends AggregateResource
{
    /**
     * @var integer
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=false)
     */
    protected $txId;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=false)
     */
    protected $method;

    /**
     * Transaction payload
     *
     * @var array
     * @ORM\Column(type="json_array", nullable=false)
     * @Assert\Type("array")
     * @Assert\NotNull()
     */
    protected $payload;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=true)
     * @Assert\Type(type="\DateTime")
     */
    protected $checked;

    /**
     * @var boolean
     * @Assert\Type(type="boolean")
     * @ORM\Column(type="boolean")
     */
    protected $verified = false;

    public function __construct()
    {
        $this->payload = array();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $txId
     */
    public function setTransactionId($txId)
    {
        $this->txId = $txId;
    }

    /**
     * @return string
     */
    public function getTransactionId()
    {
        return $this->txId;
    }

    /**
     * @return ArrayCollection
     */
    public function getPayload()
    {
        return new ArrayCollection($this->payload);
    }

    /**
     * @param ArrayCollection $payload
     */
    public function setPayload(ArrayCollection $payload)
    {
        $this->payload = $payload->toArray();
    }

    /**
     * @return void
     */
    public function verify()
    {
        $this->verified = true;
    }

    /**
     * @return bool
     */
    public function isVerified()
    {
        return $this->verified;
    }

    /**
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @param string $method
     */
    public function setMethod($method)
    {
        $this->method = $method;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getTransactionId();
    }
}

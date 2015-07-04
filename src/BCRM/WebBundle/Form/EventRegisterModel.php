<?php

namespace BCRM\WebBundle\Form;

use BCRM\BackendBundle\Entity\Event\Event;
use Symfony\Component\Validator\Constraints as Assert;

class EventRegisterModel
{
    /**
     * @var string
     * @Assert\NotBlank()
     * @Assert\Email()
     */
    public $email;

    /**
     * @var string
     * @Assert\NotBlank()
     */
    public $name;

    /**
     * @var string
     * @Assert\Regex("/^@[a-zA-Z0-9_]{1,15}$/")
     */
    public $twitter;

    /**
     * @var string
     * @Assert\NotBlank()
     */
    public $arrival;

    /**
     * @var string
     * @Assert\NotBlank()
     */
    public $food;

    /**
     * @var boolean
     * @Assert\Type(type="integer")
     * @Assert\NotBlank()
     * @Assert\Range(min=0,max=1)
     */
    public $participantList;

    /**
     * @var integer
     * @Assert\Type(type="integer")
     * @Assert\NotBlank()
     * @Assert\Range(min=1,max=3)
     */
    public $days;

    /**
     * @var string
     * @Assert\Regex(pattern="/^#[^\s]{1,15}( #[^\s]{1,15}){0,2}$/")
     */
    public $tags;


    /**
     * @var integer
     * @Assert\Type(type="integer")
     * @Assert\NotBlank()
     * @Assert\Range(min=0)
     */
    protected $donation = 0;

    /**
     * @var string
     * @Assert\Choice(choices={"barzahlen.de", "paypal"})
     */
    public $payment;

    /**
     * @var boolean
     * @Assert\Type(type="integer")
     * @Assert\NotBlank()
     * @Assert\Range(min=1,max=1,groups={"review"})
     */
    public $norefund = 0;

    /**
     * @var Event
     */
    public $event;

    /**
     * @return bool
     */
    public function wantsSaturday()
    {
        return $this->days === 1 || $this->days === 3;
    }

    /**
     * @return bool
     */
    public function wantsSunday()
    {
        return $this->days === 2 || $this->days === 3;
    }

    /**
     * @return mixed
     */
    public function getDonation()
    {
        return $this->donation;
    }

    /**
     * @param mixed $donation
     */
    public function setDonation($donation)
    {
        $this->donation = (int)$donation;
    }
}

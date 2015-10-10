<?php

namespace BCRM\WebBundle\Form;

use BCRM\BackendBundle\Entity\Event\Event;
use Carbon\Carbon;
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
     * @Assert\Regex(pattern="/^#[^\s]{1,25}( #[^\s]{1,25}){0,2}$/")
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
     * @Assert\Range(min=1,max=1,groups={"review"},minMessage="Bitte bestätigen!")
     */
    public $norefund = 0;

    /**
     * @var boolean
     * @Assert\Type(type="integer")
     * @Assert\NotBlank()
     * @Assert\Range(min=1,max=1,groups={"review"},minMessage="Bitte bestätigen!")
     */
    public $autocancel = 0;

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
        $this->donation = (int)$donation;
    }

    /**
     * @return float
     */
    public function getDonationEur()
    {
        return $this->donation / 100;
    }

    /**
     * @param float $donation
     * NOTE: currently only supports german notation of floats
     */
    public function setDonationEur($donation)
    {
        $this->donation = (int)round(floatval(str_replace(',', '.', $donation)) * 100);
    }

    /**
     * @return Carbon
     */
    public function getAutoCancelDate()
    {
        return Carbon::create()->addDays(3);
    }
}

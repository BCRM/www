<?php

namespace BCRM\WebBundle\Form;

use BCRM\BackendBundle\Entity\Event\Event;
use Symfony\Component\Validator\Constraints as Assert;

class EventUnregisterModel
{
    /**
     * @var string
     * @Assert\NotBlank()
     * @Assert\Email()
     */
    public $email;

    /**
     * @var integer
     * @Assert\Type(type="integer")
     * @Assert\NotBlank()
     * @Assert\Range(min=1,max=3)
     */
    public $days;

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
}

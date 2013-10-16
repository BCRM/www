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
     * @Assert\NotBlank()
     */
    public $arrival;

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

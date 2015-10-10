<?php

/**
 * @author    Markus Tacker <m@cto.hiv>
 * @copyright 2013-2015 Verein zur Förderung der Netzkultur im Rhein-Main-Gebiet e.V. | http://netzkultur-rheinmain.de/
 */

namespace BCRM\BackendBundle\Service\Event;

use BCRM\BackendBundle\Entity\Event\Registration;

class RegisterCommand
{
    public $uuid;

    public $event;

    public $email;

    public $name;

    public $twitter;

    public $saturday;

    public $sunday;

    public $food;

    public $tags;

    public $type = Registration::TYPE_NORMAL;

    public $participantList = false;

    public $confirmed = false;

    public $payment;

    public $donation;
}

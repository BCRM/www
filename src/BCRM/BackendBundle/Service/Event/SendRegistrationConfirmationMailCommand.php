<?php

/**
 * @author    Markus Tacker <m@coderbyheart.de>
 * @copyright 2013 Verein zur Förderung der Netzkultur im Rhein-Main-Gebiet e.V. | http://netzkultur-rheinmain.de/
 */

namespace BCRM\BackendBundle\Service\Event;

use BCRM\BackendBundle\Entity\Event\Registration;

class SendRegistrationConfirmationMailCommand
{
    /**
     * @var Registration
     */
    public $registration;

    /**
     * @var string
     */
    public $schemeAndHost;
}

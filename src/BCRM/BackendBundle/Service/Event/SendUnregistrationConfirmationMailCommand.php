<?php

/**
 * @author    Markus Tacker <m@cto.hiv>
 * @copyright 2013-2015 Verein zur Förderung der Netzkultur im Rhein-Main-Gebiet e.V. | http://netzkultur-rheinmain.de/
 */

namespace BCRM\BackendBundle\Service\Event;

use BCRM\BackendBundle\Entity\Event\Unregistration;

class SendUnregistrationConfirmationMailCommand
{
    /**
     * @var Unregistration
     */
    public $unregistration;

    /**
     * @var string
     */
    public $schemeAndHost;
}

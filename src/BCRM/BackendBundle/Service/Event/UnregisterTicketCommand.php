<?php

/**
 * @author    Markus Tacker <m@coderbyheart.de>
 * @copyright 2013 Verein zur Förderung der Netzkultur im Rhein-Main-Gebiet e.V. | http://netzkultur-rheinmain.de/
 */

namespace BCRM\BackendBundle\Service\Event;

use BCRM\BackendBundle\Entity\Event\Event;
use BCRM\BackendBundle\Entity\Event\Unregistration;

class UnregisterTicketCommand
{
    /**
     * @var Unregistration
     */
    public $unregistration;

    /**
     * @var Event
     */
    public $event;
}

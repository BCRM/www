<?php

/**
 * @author    Markus Tacker <m@coderbyheart.de>
 * @copyright 2013 Verein zur Förderung der Netzkultur im Rhein-Main-Gebiet e.V. | http://netzkultur-rheinmain.de/
 */

namespace BCRM\BackendBundle\Service\Event;

use BCRM\BackendBundle\Entity\Event\Registration;
use BCRM\BackendBundle\Entity\Event\Ticket;
use BCRM\BackendBundle\Service\Event;

/**
 * Send ticket information to the given user.
 *
 * @package BCRM\BackendBundle\Service\Event
 */
class SendTicketMailCommand
{
    /**
     * @var Ticket
     */
    public $ticket;

    /**
     * @var Event
     */
    public $event;

    /**
     * @var string
     */
    public $schemeAndHost;
}

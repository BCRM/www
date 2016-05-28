<?php

/**
 * @author    Markus Tacker <m@cto.hiv>
 * @copyright 2013-2016 Verein zur Förderung der Netzkultur im Rhein-Main-Gebiet e.V. | http://netzkultur-rheinmain.de/
 */

namespace BCRM\BackendBundle\Event\Event;

use LiteCQRS\Bus\EventMessageHeader;
use LiteCQRS\DomainEvent;

class TicketDeletedEvent implements DomainEvent
{
    /**
     * @var \BCRM\BackendBundle\Entity\Event\Ticket
     */
    public $ticket;

    private $messageHeader;

    public function __construct()
    {
        $this->messageHeader = new EventMessageHeader();
    }

    public function getEventName()
    {
        return 'TicketDeleted';
    }

    public function getMessageHeader()
    {
        return $this->messageHeader;
    }

    public function getAggregateId()
    {
        return $this->messageHeader->aggregateId;
    }
}

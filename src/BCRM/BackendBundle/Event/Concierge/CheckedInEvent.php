<?php

/**
 * @author    Markus Tacker <m@coderbyheart.com>
 * @copyright 2013-2016 Verein zur Förderung der Netzkultur im Rhein-Main-Gebiet e.V. | http://netzkultur-rheinmain.de/
 */

namespace BCRM\BackendBundle\Event\Concierge;

use LiteCQRS\Bus\EventMessageHeader;
use LiteCQRS\DomainEvent;

class CheckedInEvent implements DomainEvent
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
        return 'CheckedIn';
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

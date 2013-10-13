<?php

/**
 * @author    Markus Tacker <m@coderbyheart.de>
 * @copyright 2013 Verein zur Förderung der Netzkultur im Rhein-Main-Gebiet e.V. | http://netzkultur-rheinmain.de/
 */

namespace BCRM\BackendBundle\Entity\Event;

use Doctrine\ORM\EntityRepository;
use PhpOption\None;
use PhpOption\Some;

class DoctrineTicketRepository extends EntityRepository implements TicketRepository
{
    /**
     * @param Event $event
     *
     * @return Ticket[]
     */
    public function getNewTickets(Event $event)
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.event = :event')->setParameter('event', $event)
            ->andWhere('t.notified = 0')
            ->getQuery()
            ->getResult();
    }

}

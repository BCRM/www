<?php

/**
 * @author    Markus Tacker <m@coderbyheart.de>
 * @copyright 2013 Verein zur Förderung der Netzkultur im Rhein-Main-Gebiet e.V. | http://netzkultur-rheinmain.de/
 */

namespace BCRM\BackendBundle\Entity\Event;

use Doctrine\ORM\EntityRepository;
use PhpOption\None;
use PhpOption\Option;
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

    /**
     * @param Event  $event
     * @param string $email
     *
     * @return Ticket[]
     */
    public function getTicketsForEmail(Event $event, $email)
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.event = :event')->setParameter('event', $event)
            ->andWhere('t.email = :email')->setParameter('email', $email)
            ->getQuery()
            ->getResult();
    }

    /**
     * @param integer $id
     * @param string  $code
     *
     * @return Option
     */
    public function getTicketByIdAndCode($id, $code)
    {
        return Option::fromValue($this->createQueryBuilder('t')
            ->andWhere('t.id = :id')->setParameter('id', $id)
            ->andWhere('t.code = :code')->setParameter('code', $code)
            ->getQuery()
            ->getOneOrNullResult());
    }

    /**
     * @param Event $event
     *
     * @return Ticket[]
     */
    public function getTicketsForEvent(Event $event)
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.event = :event')->setParameter('event', $event)
            ->getQuery()
            ->getResult();
    }
}

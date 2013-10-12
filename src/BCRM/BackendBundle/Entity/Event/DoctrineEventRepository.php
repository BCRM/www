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

class DoctrineEventRepository extends EntityRepository implements EventRepository
{
    /**
     * @return Option
     */
    public function getNextEvent()
    {
        $qb = $this->createQueryBuilder('e');
        $qb->andWhere('e.registrationStart <= :now');
        $qb->setParameter('now', new \DateTime());
        $qb->setMaxResults(1);
        $qb->orderBy('e.start', 'ASC');
        $event = $qb->getQuery()->getOneOrNullResult();
        return $event == null ? None::create() : new Some($event);
    }

    /**
     * @param Event $event
     * @param       $day
     *
     * @return integer
     */
    public function getCapacity(Event $event, $day)
    {
        return $this->createQueryBuilder('e')
            ->select('e.capacity - COUNT(t.id) as capacity')
            ->leftJoin('e.tickets', 't')
            ->andWhere('t.day = :day')->setParameter('day', $day)
            ->andWhere('e.id = :event')->setParameter('event', $event->getId())
            ->getQuery()
            ->getSingleScalarResult();
    }


}

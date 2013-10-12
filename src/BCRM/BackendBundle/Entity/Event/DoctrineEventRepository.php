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
        $qb->andWhere('e.start >= :now');
        $qb->setParameter('now', new \DateTime());
        $qb->setMaxResults(1);
        $qb->orderBy('e.start', 'ASC');
        $event = $qb->getQuery()->getOneOrNullResult();
        return $event == null ? None::create() : new Some($event);
    }

}

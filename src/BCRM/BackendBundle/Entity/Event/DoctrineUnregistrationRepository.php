<?php

/**
 * @author    Markus Tacker <m@cto.hiv>
 * @copyright 2013-2016 Verein zur Förderung der Netzkultur im Rhein-Main-Gebiet e.V. | http://netzkultur-rheinmain.de/
 */

namespace BCRM\BackendBundle\Entity\Event;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use PhpOption\None;
use PhpOption\Some;

class DoctrineUnregistrationRepository extends EntityRepository implements UnregistrationRepository
{
    /**
     * @return Unregistration[]
     */
    public function getNewUnregistrations()
    {
        $qb = $this->createQueryBuilder('u');
        $qb->andWhere('u.confirmed=0');
        $qb->andWhere('u.confirmationKey IS NULL');
        $qb->groupBy('u.email');
        return $qb->getQuery()->getResult();
    }

    /**
     * @param Event $event
     *
     * @return Unregistration[]
     */
    public function getUnprocessedUnregistrations(Event $event)
    {
        $rsm = new ResultSetMappingBuilder($this->_em);
        $rsm->addRootEntityFromClassMetadata('BCRM\BackendBundle\Entity\Event\Unregistration', 'u');
        $eventId         = $event->getId();
        $unregistrations = "SELECT MAX(id) FROM unregistration WHERE confirmed = 1 AND event_id = $eventId AND processed = 0 GROUP BY event_id, email ORDER BY created ASC, id ASC";
        $sql             = "SELECT * FROM unregistration WHERE id IN ($unregistrations)";
        $query           = $this->_em->createNativeQuery(
            $sql,
            $rsm
        );
        return $query->getResult();
    }

    /**
     * @param string $id
     * @param string $key
     *
     * @return \PhpOption\Option
     */
    public function getUnregistrationByIdAndKey($id, $key)
    {
        $qb = $this->createQueryBuilder('u');
        $qb->andWhere('u.id = :id')->setParameter('id', $id);
        $qb->andWhere('u.confirmationKey = :key')->setParameter('key', $key);
        $result = $qb->getQuery()->getOneOrNullResult();
        return $result === null ? None::create() : new Some($result);
    }

    /**
     * @param Event $event
     *
     * @return Unregistration[]
     */
    public function getUnregistrationsForEvent(Event $event)
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.event = :event')->setParameter('event', $event)
            ->getQuery()
            ->getResult();
    }


}

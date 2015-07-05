<?php

/**
 * @author    Markus Tacker <m@cto.hiv>
 * @copyright 2013-2015 Verein zur Förderung der Netzkultur im Rhein-Main-Gebiet e.V. | http://netzkultur-rheinmain.de/
 */

namespace BCRM\BackendBundle\Entity\Event;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use PhpOption\None;
use PhpOption\Option;
use PhpOption\Some;
use Doctrine\ORM\Query\Expr;

class DoctrineRegistrationRepository extends EntityRepository implements RegistrationRepository
{
    /**
     * @return Registration[]
     */
    public function getNewRegistrations()
    {
        $qb = $this->createQueryBuilder('r');
        $qb->andWhere('r.confirmed=0');
        $qb->andWhere('r.confirmationKey IS NULL');
        $qb->groupBy('r.email');
        return $qb->getQuery()->getResult();
    }

    /**
     * @param string $id
     * @param string $key
     *
     * @return \PhpOption\Option
     */
    public function getRegistrationByIdAndKey($id, $key)
    {
        $qb = $this->createQueryBuilder('r');
        $qb->andWhere('r.id = :id')->setParameter('id', $id);
        $qb->andWhere('r.confirmationKey = :key')->setParameter('key', $key);
        $subscription = $qb->getQuery()->getOneOrNullResult();
        return $subscription === null ? None::create() : new Some($subscription);
    }

    /**
     * {@inheritDocs}
     */
    public function getNextRegistrations(Event $event, $day, $capacity)
    {
        $rsm = new ResultSetMappingBuilder($this->_em);
        $rsm->addRootEntityFromClassMetadata('BCRM\BackendBundle\Entity\Event\Registration', 'r');

        $type          = Registration::TYPE_NORMAL;
        $eventId       = $event->getId();
        $dayName       = $day == Ticket::DAY_SATURDAY ? 'saturday' : 'sunday';
        $registrations = "SELECT MAX(id) FROM registration WHERE confirmed = 1 AND type = $type AND event_id = $eventId GROUP BY event_id, email ORDER BY created ASC, id ASC";
        $sql           = "SELECT * FROM registration WHERE id IN ($registrations) AND email NOT IN (SELECT email FROM ticket WHERE day = $day AND event_id = $eventId) AND $dayName = 1 LIMIT $capacity";
        $query         = $this->_em->createNativeQuery(
            $sql,
            $rsm
        );
        return $query->getResult();
    }

    /**
     * {@inheritDocs}
     */
    public function getNextVipRegistrations(Event $event, $day)
    {
        $rsm = new ResultSetMappingBuilder($this->_em);
        $rsm->addRootEntityFromClassMetadata('BCRM\BackendBundle\Entity\Event\Registration', 'r');
        $types         = join(',', array(Registration::TYPE_SPONSOR, Registration::TYPE_VIP));
        $eventId       = $event->getId();
        $dayName       = $day == Ticket::DAY_SATURDAY ? 'saturday' : 'sunday';
        $registrations = "SELECT MAX(id) FROM registration WHERE confirmed = 1 AND type IN ($types) AND event_id = $eventId GROUP BY event_id, email ORDER BY created ASC, id ASC";
        $sql           = "SELECT * FROM registration WHERE id IN ($registrations) AND email NOT IN (SELECT email FROM ticket WHERE day = $day AND event_id = $eventId) AND $dayName = 1";
        $query         = $this->_em->createNativeQuery(
            $sql,
            $rsm
        );
        return $query->getResult();
    }


    /**
     * @param Event  $event
     * @param string $email
     *
     * @return Option
     */
    public function getRegistrationForEmail(Event $event, $email)
    {
        return Option::fromValue($this->createQueryBuilder('r')
            ->andWhere('r.event = :event')->setParameter('event', $event)
            ->andWhere('r.email = :email')->setParameter('email', $email)
            ->andWhere('r.confirmed = 1')
            ->orderBy('r.created', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult());
    }

    /**
     * {@inheritdoc}
     */
    public function getParticipantList(Event $event)
    {

        $qb = $this->createQueryBuilder('r');
        return new ArrayCollection($qb
            ->where('r.event = :event')->setParameter('event', $event)
            ->andWhere($qb->expr()->in('r.email',
                $this->getEntityManager()->createQueryBuilder()
                    ->select('t.email')
                    ->from('BCRMBackendBundle:Event\Ticket', 't')
                    ->where('t.event = :event')->setParameter('event', $event)
                    ->getDQL()
            ))
            ->andWhere($qb->expr()->in('r.id',
                $this->createQueryBuilder('r2')
                    ->select('MAX(r2.id)')
                    ->where('r2.event = :event')->setParameter('event', $event)
                    ->andWhere('r2.participantList = 1')
                    ->andWhere('r2.confirmed = 1')
                    ->groupBy('r2.email')
                    ->getDQL()
            ))
            ->orderBy('r.name')
            ->getQuery()
            ->getResult());
    }
}

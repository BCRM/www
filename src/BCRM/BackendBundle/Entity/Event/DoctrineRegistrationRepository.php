<?php

/**
 * @author    Markus Tacker <m@cto.hiv>
 * @copyright 2013-2016 Verein zur Förderung der Netzkultur im Rhein-Main-Gebiet e.V. | http://netzkultur-rheinmain.de/
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
    public function getToPay()
    {
        $qb = $this->createQueryBuilder('r');
        $qb->andWhere('r.payment IS NULL');
        $qb->andWhere('r.paymentNotified IS NULL');
        return $qb->getQuery()->getResult();
    }

    /**
     * @param string $uuid
     *
     * @return \PhpOption\Option
     */
    public function getRegistrationByUuid($uuid)
    {
        $qb = $this->createQueryBuilder('r');
        $qb->andWhere('r.uuid = :uuid')->setParameter('uuid', $uuid);
        $registration = $qb->getQuery()->getOneOrNullResult();
        return $registration === null ? None::create() : new Some($registration);
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
        $registrations = "SELECT MAX(id) FROM registration WHERE type = $type AND event_id = $eventId GROUP BY event_id, email ORDER BY created ASC, id ASC";
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
        $registrations = "SELECT MAX(id) FROM registration WHERE type IN ($types) AND event_id = $eventId GROUP BY event_id, email ORDER BY created ASC, id ASC";
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
            ->andWhere('r.payment IS NOT NULL')
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
                    ->andWhere('r2.payment IS NOT NULL')
                    ->groupBy('r2.email')
                    ->getDQL()
            ))
            ->orderBy('r.name')
            ->getQuery()
            ->getResult());
    }

    /**
     * Find a registration by uuid
     *
     * @param string $uuid
     *
     * @return Option of Registration
     */
    public function findByUuid($uuid)
    {
        return Option::fromValue($this->createQueryBuilder('r')
            ->andWhere('r.uuid = :uuid')->setParameter('uuid', $uuid)
            ->orderBy('r.id', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult());
    }
}

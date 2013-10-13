<?php

/**
 * @author    Markus Tacker <m@coderbyheart.de>
 * @copyright 2013 Verein zur Förderung der Netzkultur im Rhein-Main-Gebiet e.V. | http://netzkultur-rheinmain.de/
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
     * @param Unregistration $registration
     *
     * @return void
     */
    public function confirmUnregistration(Unregistration $unregistration)
    {
        $this->createQueryBuilder('u')
            ->update()
            ->set('u.confirmed', '1')
            ->where('u.email = :email')->setParameter('email', $unregistration->getEmail())
            ->andWhere('u.confirmed = 0')
            ->getQuery()
            ->execute();
    }

    /**
     * @param Unregistration $registration
     * @param string         $key
     *
     * @return void
     */
    public function initConfirmation(Unregistration $unregistration, $key)
    {
        $this->createQueryBuilder('u')
            ->update()
            ->set('u.confirmationKey', ':key')->setParameter('key', $key)
            ->where('u.email = :email')->setParameter('email', $unregistration->getEmail())
            ->andWhere('u.confirmed = 0')
            ->getQuery()
            ->execute();
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
}

<?php

/**
 * @author    Markus Tacker <m@coderbyheart.de>
 * @copyright 2013 Verein zur Förderung der Netzkultur im Rhein-Main-Gebiet e.V. | http://netzkultur-rheinmain.de/
 */

namespace BCRM\BackendBundle\Entity\Event;

use Doctrine\ORM\EntityRepository;
use PhpOption\None;
use PhpOption\Some;

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
     * @param Registration $registration
     *
     * @return void
     */
    public function confirmRegistration(Registration $registration)
    {
        $this->createQueryBuilder('r')
            ->update()
            ->set('r.confirmed', '1')
            ->where('r.email = :email')->setParameter('email', $registration->getEmail())
            ->andWhere('r.confirmed = 0')
            ->getQuery()
            ->execute();
    }

    /**
     * @param Registration $registration
     * @param string       $key
     *
     * @return void
     */
    public function initConfirmation(Registration $registration, $key)
    {
        $this->createQueryBuilder('r')
            ->update()
            ->set('r.confirmationKey', ':key')->setParameter('key', $key)
            ->where('r.email = :email')->setParameter('email', $registration->getEmail())
            ->andWhere('r.confirmed = 0')
            ->getQuery()
            ->execute();
    }


}

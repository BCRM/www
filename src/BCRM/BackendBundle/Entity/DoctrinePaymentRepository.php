<?php

/**
 * @author    Markus Tacker <m@cto.hiv>
 * @copyright 2013-2015 Verein zur Förderung der Netzkultur im Rhein-Main-Gebiet e.V. | http://netzkultur-rheinmain.de/
 */

namespace BCRM\BackendBundle\Entity;

use Doctrine\ORM\EntityRepository;

class DoctrinePaymentRepository extends EntityRepository implements PaymentRepository
{
    /**
     * @return Payment[]
     */
    public function getUnchecked()
    {
        $qb = $this->createQueryBuilder('p');
        $qb->andWhere('p.checked IS NULL');
        return $qb->getQuery()->getResult();
    }

}
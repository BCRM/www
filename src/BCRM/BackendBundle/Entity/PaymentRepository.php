<?php

/**
 * @author    Markus Tacker <m@cto.hiv>
 * @copyright 2013-2015 Verein zur Förderung der Netzkultur im Rhein-Main-Gebiet e.V. | http://netzkultur-rheinmain.de/
 */

namespace BCRM\BackendBundle\Entity;

use PhpOption\Option;

interface PaymentRepository
{
    /**
     * @return Payment[]
     */
    public function getUnchecked();

    /**
     * @return Option of Payment
     */
    public function findByTxId($txId);
}

<?php

/**
 * @author    Markus Tacker <m@cto.hiv>
 * @copyright 2013-2016 Verein zur Förderung der Netzkultur im Rhein-Main-Gebiet e.V. | http://netzkultur-rheinmain.de/
 */

namespace BCRM\BackendBundle\Service\Event;

use BCRM\BackendBundle\Entity\Event\Registration;
use BCRM\BackendBundle\Entity\Event\Unregistration;

class ConfirmUnregistrationCommand
{
    /**
     * @var Unregistration
     */
    public $unregistration;
}

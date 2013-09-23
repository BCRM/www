<?php

/**
 * @author    Markus Tacker <m@coderbyheart.de>
 * @copyright 2013 Verein zur Förderung der Netzkultur im Rhein-Main-Gebiet e.V. | http://netzkultur-rheinmain.de/
 */

namespace BCRM\BackendBundle\Service\Newsletter;

use BCRM\BackendBundle\Entity\Newsletter\Subscription;

class SendConfirmationMailCommand
{
    /**
     * @var Subscription
     */
    public $subscription;

    /**
     * @var string
     */
    public $schemeAndHost;
}
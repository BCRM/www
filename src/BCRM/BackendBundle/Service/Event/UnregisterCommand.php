<?php

/**
 * @author    Markus Tacker <m@coderbyheart.com>
 * @copyright 2013-2016 Verein zur Förderung der Netzkultur im Rhein-Main-Gebiet e.V. | http://netzkultur-rheinmain.de/
 */

namespace BCRM\BackendBundle\Service\Event;

class UnregisterCommand
{
    public $event;

    public $email;

    public $saturday;

    public $sunday;
    
    public $confirmed = false;
}

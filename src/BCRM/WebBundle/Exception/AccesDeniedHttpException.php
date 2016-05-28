<?php

/**
 * @author    Markus Tacker <m@cto.hiv>
 * @copyright 2013-2016 Verein zur Förderung der Netzkultur im Rhein-Main-Gebiet e.V. | http://netzkultur-rheinmain.de/
 */

namespace BCRM\WebBundle\Exception;

use BCRM\WebBundle\Exception;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class AccesDeniedHttpException extends AccessDeniedHttpException implements Exception
{
}

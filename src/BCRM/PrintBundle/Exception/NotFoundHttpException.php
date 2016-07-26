<?php

/**
 * @author    Markus Tacker <m@coderbyheart.com>
 * @copyright 2013-2016 Verein zur Förderung der Netzkultur im Rhein-Main-Gebiet e.V. | http://netzkultur-rheinmain.de/
 */

namespace BCRM\PrintBundle\Exception;

use BCRM\PrintBundle\Exception;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException as Base;

class NotFoundHttpException extends Base implements Exception
{
}

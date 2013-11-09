<?php

/**
 * @author Markus Tacker <m@coderbyheart.de>
 */

namespace BCRM\PrintBundle\Exception;

use BCRM\PrintBundle\Exception;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class AccesDeniedHttpException extends AccessDeniedHttpException implements Exception
{
}
<?php

/**
 * @author Markus Tacker <m@coderbyheart.de>
 */

namespace BCRM\WebBundle\Exception;

use BCRM\WebBundle\Exception;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class AccesDeniedHttpException extends AccessDeniedHttpException implements Exception
{
}
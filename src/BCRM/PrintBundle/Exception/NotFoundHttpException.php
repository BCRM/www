<?php

/**
 * @author Markus Tacker <m@coderbyheart.de>
 */

namespace BCRM\PrintBundle\Exception;

use BCRM\PrintBundle\Exception;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException as Base;

class NotFoundHttpException extends Base implements Exception
{
}
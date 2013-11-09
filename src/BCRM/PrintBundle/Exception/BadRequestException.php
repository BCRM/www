<?php

/**
 * @author Markus Tacker <m@coderbyheart.de>
 */

namespace BCRM\PrintBundle\Exception;

use BCRM\PrintBundle\Exception;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class BadRequestException extends BadRequestHttpException implements Exception
{
}
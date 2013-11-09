<?php

/**
 * @author Markus Tacker <m@coderbyheart.de>
 */

namespace BCRM\WebBundle\Exception;

use BCRM\WebBundle\Exception;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class BadRequestException extends BadRequestHttpException implements Exception
{
}
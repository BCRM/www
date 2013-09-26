<?php
/**
 * @author Markus Tacker <m@coderbyheart.de>
 */

namespace BCRM\BackendBundle\Exception;

use BCRM\BackendBundle\Exception;

use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException as SymfonyFileNotFoundException;

class FileNotFoundException extends SymfonyFileNotFoundException implements Exception
{

}

<?php

/**
 * @author    Markus Tacker <m@coderbyheart.com>
 * @copyright 2013-2016 Verein zur Förderung der Netzkultur im Rhein-Main-Gebiet e.V. | http://netzkultur-rheinmain.de/
 */

namespace BCRM\BackendBundle\Content;

interface ContentReader
{
    /**
     * @param string $path
     *
     * @return Content
     */
    public function getContent($path);

    /**
     * @param string $path
     *
     * @return Info
     */
    public function getInfo($path);
}

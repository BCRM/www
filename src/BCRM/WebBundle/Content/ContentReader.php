<?php

/**
 * @author    Markus Tacker <m@cto.hiv>
 * @copyright 2013-2016 Verein zur Förderung der Netzkultur im Rhein-Main-Gebiet e.V. | http://netzkultur-rheinmain.de/
 */

namespace BCRM\WebBundle\Content;

use BCRM\BackendBundle\Content\Info;

interface ContentReader
{
    /**
     * @param string $page
     *
     * @return Page
     */
    public function getPage($page);

    /**
     * @param string $page
     *
     * @return Info
     */
    public function getInfo($page);
}

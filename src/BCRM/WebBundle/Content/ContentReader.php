<?php

/**
 * @author    Markus Tacker <m@coderbyheart.de>
 * @copyright 2013 Verein zur Förderung der Netzkultur im Rhein-Main-Gebiet e.V. | http://netzkultur-rheinmain.de/
 */

namespace BCRM\WebBundle\Content;

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
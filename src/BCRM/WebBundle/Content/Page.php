<?php

/**
 * @author    Markus Tacker <m@coderbyheart.de>
 * @copyright 2013 Verein zur Förderung der Netzkultur im Rhein-Main-Gebiet e.V. | http://netzkultur-rheinmain.de/
 */

namespace BCRM\WebBundle\Content;

use BCRM\BackendBundle\Content\Info;
use Doctrine\Common\Collections\ArrayCollection;

class Page extends Info
{
    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     */
    protected $subnav;

    public function __construct()
    {
        $this->subnav     = new ArrayCollection();
        $this->properties = new ArrayCollection(
            array('subnav' => 1)
        );
    }

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getSubnav()
    {
        return $this->subnav;
    }

    /**
     * @param \Doctrine\Common\Collections\ArrayCollection $subnav
     */
    public function setSubnav($subnav)
    {
        $this->subnav = $subnav;
    }

    /**
     * Returns whether this page should not be displayed.
     * 
     * @return bool
     */
    public function isHidden()
    {
        return $this->getProperties()->containsKey('hidden') && $this->getProperties()->get('hidden') == 1;
    }
}

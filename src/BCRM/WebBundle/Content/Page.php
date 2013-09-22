<?php

/**
 * @author    Markus Tacker <m@coderbyheart.de>
 * @copyright 2013 Verein zur Förderung der Netzkultur im Rhein-Main-Gebiet e.V. | http://netzkultur-rheinmain.de/
 */

namespace BCRM\WebBundle\Content;

use Doctrine\Common\Collections\ArrayCollection;

class Page extends Info
{
    /**
     * @var string
     */
    protected $content;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     */
    protected $subnav;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     */
    protected $properties;

    public function __construct()
    {
        $this->subnav     = new ArrayCollection();
        $this->properties = new ArrayCollection(
            array('subnav' => 1)
        );
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param string $content
     */
    public function setContent($content)
    {
        $this->content = $content;
    }

    /**
     * @param int $order
     */
    public function setOrder($order)
    {
        $this->order = (int)$order;
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
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getProperties()
    {
        return $this->properties;
    }

    /**
     * @param \Doctrine\Common\Collections\ArrayCollection $properties
     */
    public function setProperties(ArrayCollection $properties)
    {
        $this->properties = $properties;
    }


}